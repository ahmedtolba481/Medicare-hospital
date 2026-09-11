<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Department;
use App\Models\Doctor;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class PublicSiteControllerTest extends TestCase
{
    use RefreshDatabase;

    #[TestWith(['home', 'Expert care for every chapter of life.'])]
    #[TestWith(['about', 'Why choose MediCare?'])]
    #[TestWith(['services', 'Specialist consultations'])]
    #[TestWith(['contact', 'care@medicare.test'])]
    public function test_guests_can_read_public_pages(string $routeName, string $content): void
    {
        $this->get(route($routeName))->assertSeeText($content);

        $this->assertGuest();
    }

    public function test_navigation_and_footer_links_resolve_for_guests(): void
    {
        $response = $this->get(route('home'));
        $document = new DOMDocument;
        $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($document);

        foreach (['nav', 'footer'] as $element) {
            $links = $xpath->query('//'.$element.'//a[@href]');
            $this->assertGreaterThan(0, $links->length);

            foreach ($links as $link) {
                $this->get($link->getAttribute('href'))->assertOk();
            }
        }
    }

    public function test_home_previews_database_records_and_counts_the_entire_care_team(): void
    {
        $departments = Department::factory()->count(4)->sequence(
            ['name' => 'Cardiology'], ['name' => 'Dentistry'],
            ['name' => 'Neurology'], ['name' => 'Pediatrics'],
        )->create();
        $doctors = Doctor::factory()->count(4)->for($departments->first())->create();

        $response = $this->get(route('home'));

        $response->assertViewHas('departmentCount', 4)
            ->assertViewHas('doctorCount', 4)
            ->assertSeeText(['Cardiology', 'Dentistry', 'Neurology', $doctors[0]->user->name, $doctors[2]->user->name])
            ->assertDontSeeText(['Pediatrics', $doctors[3]->user->name])
            ->assertSeeText(['Our main services', 'Book an appointment'])
            ->assertSee('href="'.route('contact').'"', false);

        $this->get(route('doctors.show', $doctors[0]))->assertSeeText($doctors[0]->user->name);
    }

    public function test_about_counts_database_records_and_explains_the_hospital_approach(): void
    {
        $department = Department::factory()->create();
        Doctor::factory()->count(2)->for($department)->create();

        $this->get(route('about'))
            ->assertViewHas('departmentCount', 1)
            ->assertViewHas('doctorCount', 2)
            ->assertSeeText(['Our mission', 'Our vision', 'Why choose MediCare?']);
    }

    public function test_home_has_helpful_empty_states_without_demo_records(): void
    {
        $this->get(route('home'))
            ->assertViewHas('departmentCount', 0)
            ->assertViewHas('doctorCount', 0)
            ->assertSeeText(['Department information will be available soon.', 'Doctor profiles will be available soon.']);
    }

    public function test_contact_submission_saves_an_unread_message_and_displays_success(): void
    {
        $payload = $this->contactPayload();

        $response = $this->post(route('contact.store'), [...$payload, 'status' => 'read', 'id' => 999]);

        $response->assertRedirect(route('contact'))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('contact_messages', 1);
        $this->assertDatabaseHas('contact_messages', [...$payload, 'status' => 'unread']);
        $this->assertDatabaseMissing('contact_messages', ['id' => 999]);

        $this->get(route('contact'))->assertSeeText('Thank you. Our care team will be in touch soon.');
        $this->assertGuest();
    }

    public function test_contact_accepts_missing_optional_phone_and_subject(): void
    {
        $payload = $this->contactPayload();
        unset($payload['phone'], $payload['subject']);

        $this->post(route('contact.store'), $payload)
            ->assertRedirect(route('contact'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('contact_messages', [...$payload, 'phone' => null, 'subject' => null, 'status' => 'unread']);
    }

    public function test_contact_rejects_missing_required_fields_without_saving(): void
    {
        $this->from(route('contact'))->post(route('contact.store'), [])
            ->assertRedirect(route('contact'))
            ->assertSessionHasErrors([
                'name' => 'The name field is required.',
                'email' => 'The email field is required.',
                'message' => 'The message field is required.',
            ]);

        $this->assertDatabaseEmpty('contact_messages');
    }

    #[DataProvider('invalidContactFields')]
    public function test_contact_rejects_invalid_fields_and_displays_errors(string $field, mixed $value, string $error): void
    {
        $payload = [...$this->contactPayload(), $field => $value];

        $this->followingRedirects()->from(route('contact'))->post(route('contact.store'), $payload)
            ->assertSeeText(['Please correct the highlighted fields', $error])
            ->assertSee('aria-describedby="'.$field.'-error"', false);

        $this->assertDatabaseEmpty('contact_messages');
    }

    /** @return array<string, array{string, mixed, string}> */
    public static function invalidContactFields(): array
    {
        return [
            'invalid email' => ['email', 'invalid-email', 'The email field must be a valid email address.'],
            'long name' => ['name', str_repeat('a', 256), 'The name field must not be greater than 255 characters.'],
            'long email' => ['email', str_repeat('a', 64).'@'.str_repeat('b', 63).'.'.str_repeat('c', 63).'.'.str_repeat('d', 61).'.test', 'The email field must be a valid email address.'],
            'long phone' => ['phone', str_repeat('1', 31), 'The phone field must not be greater than 30 characters.'],
            'long subject' => ['subject', str_repeat('a', 256), 'The subject field must not be greater than 255 characters.'],
            'long message' => ['message', str_repeat('a', 5001), 'The message field must not be greater than 5000 characters.'],
            'non-string name' => ['name', 123, 'The name field must be a string.'],
            'non-string phone' => ['phone', 123, 'The phone field must be a string.'],
            'non-string subject' => ['subject', 123, 'The subject field must be a string.'],
            'non-string message' => ['message', 123, 'The message field must be a string.'],
            'array name' => ['name', ['unexpected'], 'The name field must be a string.'],
            'array email' => ['email', ['unexpected'], 'The email field must be a valid email address.'],
            'array phone' => ['phone', ['unexpected'], 'The phone field must be a string.'],
            'array subject' => ['subject', ['unexpected'], 'The subject field must be a string.'],
            'array message' => ['message', ['unexpected'], 'The message field must be a string.'],
        ];
    }

    public function test_contact_escapes_old_input_after_validation_fails(): void
    {
        $unsafeText = '<script>alert("unsafe")</script>';
        $payload = array_fill_keys(['name', 'phone', 'subject', 'message'], $unsafeText);
        $payload['email'] = $unsafeText;

        $this->followingRedirects()->from(route('contact'))->post(route('contact.store'), $payload)
            ->assertSee($unsafeText)
            ->assertDontSee($unsafeText, false);
        $this->assertDatabaseEmpty('contact_messages');
    }

    public function test_contact_preserves_valid_input_after_validation_fails(): void
    {
        $this->followingRedirects()->from(route('contact'))
            ->post(route('contact.store'), [...$this->contactPayload(), 'email' => 'invalid-email'])
            ->assertSee('value="Alex Patient"', false)
            ->assertSee('value="+1-555-0100"', false)
            ->assertSee('value="Appointment enquiry"', false)
            ->assertSeeText('Please help me arrange a specialist consultation.');

        $this->assertDatabaseEmpty('contact_messages');
    }

    public function test_contact_form_includes_csrf_token(): void
    {
        $this->get(route('contact'))->assertSee('name="_token"', false);
    }

    public function test_contact_rejects_a_submission_without_a_csrf_token(): void
    {
        $this->app->instance('env', 'local');

        $this->post(route('contact.store'), $this->contactPayload())->assertStatus(419);

        $this->assertDatabaseEmpty('contact_messages');
    }

    public function test_contact_accepts_a_submission_with_a_matching_csrf_token(): void
    {
        $this->app->instance('env', 'local');
        $payload = $this->contactPayload();

        $this->withSession(['_token' => 'contact-form-token'])
            ->post(route('contact.store'), [...$payload, '_token' => 'contact-form-token'])
            ->assertRedirect(route('contact'));

        $this->assertDatabaseHas('contact_messages', $payload);
    }

    public function test_contact_accepts_fields_at_their_length_limits(): void
    {
        $payload = [...$this->contactPayload(),
            'name' => str_repeat('a', 255),
            'phone' => str_repeat('1', 30),
            'subject' => str_repeat('a', 255),
            'message' => str_repeat('a', 5000),
        ];

        $this->post(route('contact.store'), $payload)
            ->assertRedirect(route('contact'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('contact_messages', $payload);
    }

    /** @return array{name: string, email: string, phone: string, subject: string, message: string, status: string} */
    private function contactPayload(): array
    {
        return ContactMessage::factory()->raw([
            'name' => 'Alex Patient',
            'email' => 'alex@example.test',
            'phone' => '+1-555-0100',
            'subject' => 'Appointment enquiry',
            'message' => 'Please help me arrange a specialist consultation.',
        ]);
    }
}
