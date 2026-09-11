<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\ContactMessage;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class PatientAreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_every_patient_endpoint(): void
    {
        foreach ($this->patientEndpoints() as [$method, $path]) {
            $this->call($method, $path)->assertRedirect(route('login'));
        }

        $this->assertDatabaseEmpty('contact_messages');
    }

    #[TestWith(['doctor'])]
    #[TestWith(['admin'])]
    public function test_other_roles_cannot_access_patient_endpoints(string $role): void
    {
        $this->actingAs(User::factory()->create(['role' => $role]));

        foreach ($this->patientEndpoints() as [$method, $path]) {
            $this->call($method, $path)->assertForbidden();
        }

        $this->assertDatabaseEmpty('contact_messages');
    }

    public function test_patient_pages_have_navigation_and_empty_states(): void
    {
        $patient = User::factory()->create();

        $this->actingAs($patient)->get(route('patient.dashboard'))
            ->assertSeeText(['Welcome, '.$patient->name, 'You have no upcoming appointments.', 'Profile summary']);

        foreach (['patient.dashboard', 'patient.appointments.index', 'patient.profile', 'patient.messages.index'] as $routeName) {
            $this->get(route($routeName))->assertSeeText(['Dashboard', 'My Appointments', 'Profile', 'Messages']);
        }

        $this->get(route('home'))->assertSee('href="'.route('patient.dashboard').'"', false);
    }

    public function test_dashboard_statistics_and_upcoming_visits_are_scoped_to_the_patient(): void
    {
        $this->travelTo(Carbon::parse('2026-09-11 12:00:00'));
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        $attributes = [
            ['appointment_date' => '2026-09-11', 'appointment_time' => '10:00:00', 'status' => 'confirmed'],
            ['appointment_date' => '2026-09-11', 'appointment_time' => '13:00:00', 'status' => 'pending'],
            ['appointment_date' => '2026-09-12', 'appointment_time' => '09:00:00', 'status' => 'confirmed'],
            ['appointment_date' => '2026-09-13', 'appointment_time' => '09:00:00', 'status' => 'completed'],
            ['appointment_date' => '2026-09-14', 'appointment_time' => '09:00:00', 'status' => 'cancelled'],
            ['appointment_date' => '2026-09-15', 'appointment_time' => '09:00:00', 'status' => 'rejected'],
            ['appointment_date' => '2026-09-10', 'appointment_time' => '09:00:00', 'status' => 'completed'],
        ];
        $appointments = [];
        foreach ($attributes as $index => $attribute) {
            $appointments[] = Appointment::factory()->for($patient, 'patient')->for($doctor)
                ->create([...$attribute, 'created_at' => now()->subMinutes($index)]);
        }
        $otherAppointment = Appointment::factory()->for($doctor)->create([
            'appointment_date' => '2026-09-11', 'appointment_time' => '14:00:00', 'reason' => 'Another patient private reason',
        ]);

        $this->actingAs($patient)->get(route('patient.dashboard'))
            ->assertViewHas('totalAppointments', 7)
            ->assertViewHas('upcomingCount', 2)
            ->assertViewHas('upcomingAppointments', fn ($visits): bool => $visits->modelKeys() === [$appointments[1]->id, $appointments[2]->id])
            ->assertViewHas('recentAppointments', fn ($visits): bool => $visits->modelKeys() === array_map(fn ($visit): int => $visit->id, array_slice($appointments, 0, 5)))
            ->assertViewHas('statusCounts', fn ($counts): bool => (int) $counts['completed'] === 2 && (int) $counts['confirmed'] === 2)
            ->assertDontSee('href="'.route('patient.appointments.show', $otherAppointment).'"', false);
    }

    public function test_appointment_history_and_details_never_expose_another_patient_records(): void
    {
        $patient = User::factory()->create();
        $ownAppointment = Appointment::factory()->for($patient, 'patient')->create(['reason' => 'My consultation']);
        $otherAppointment = Appointment::factory()->create(['reason' => 'Private other consultation']);

        $this->actingAs($patient)->get(route('patient.appointments.index', ['patient_id' => $otherAppointment->patient_id]))
            ->assertSeeText($ownAppointment->doctor->user->name)
            ->assertDontSeeText($otherAppointment->doctor->user->name);
        $this->get(route('patient.appointments.show', $ownAppointment))->assertSeeText('My consultation');
        $this->get(route('patient.appointments.show', $otherAppointment))->assertNotFound();
        $this->get(route('patient.appointments.show', 999999))->assertNotFound();
    }

    public function test_appointment_history_is_paginated_without_losing_patient_scope(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        Appointment::factory()->count(16)->for($patient, 'patient')->for($doctor)
            ->sequence(fn ($sequence): array => ['appointment_date' => Carbon::parse('2026-10-01')->addDays($sequence->index)->toDateString()])
            ->create();
        $otherAppointment = Appointment::factory()->create();

        $this->actingAs($patient)->get(route('patient.appointments.index', ['page' => 2]))
            ->assertViewHas('appointments', fn ($visits): bool => $visits->total() === 16 && $visits->count() === 1)
            ->assertDontSeeText($otherAppointment->doctor->user->name);
    }

    public function test_profile_updates_only_the_authenticated_patient_and_ignores_privileged_fields(): void
    {
        $patient = User::factory()->create(['email_verified_at' => now()]);
        $otherPatient = User::factory()->create();
        $password = $patient->password;

        $this->actingAs($patient)->followingRedirects()->patch(route('patient.profile.update'), [
            'id' => $otherPatient->id, 'patient_id' => $otherPatient->id, 'role' => 'admin', 'password' => 'ChangedPassword',
            'name' => 'Updated Patient', 'email' => 'updated@example.test',
            'phone' => '123456', 'address' => '42 Care Street', 'date_of_birth' => '1990-01-02',
        ])->assertSeeText('Your profile has been updated.');

        $this->assertDatabaseHas('users', ['id' => $patient->id, 'name' => 'Updated Patient', 'email' => 'updated@example.test',
            'role' => 'patient', 'phone' => '123456', 'address' => '42 Care Street', 'date_of_birth' => '1990-01-02', 'email_verified_at' => null]);
        $this->assertSame($password, $patient->fresh()->password);
        $this->assertSame($otherPatient->name, $otherPatient->fresh()->name);
        $this->get(route('patient.profile', ['patient_id' => $otherPatient->id]))->assertDontSee($otherPatient->email);
    }

    public function test_profile_accepts_unchanged_email_and_preserves_verification(): void
    {
        $patient = User::factory()->create();

        $this->actingAs($patient)->patch(route('patient.profile.update'), ['name' => $patient->name, 'email' => $patient->email])
            ->assertRedirect(route('patient.profile'))->assertSessionHasNoErrors();

        $this->assertNotNull($patient->fresh()->email_verified_at);
    }

    public function test_profile_rejects_another_users_email_without_changing_either_account(): void
    {
        $patient = User::factory()->create();
        $otherPatient = User::factory()->create();

        $this->actingAs($patient)->from(route('patient.profile'))->patch(route('patient.profile.update'), [
            'name' => 'Attempted change', 'email' => $otherPatient->email,
        ])->assertSessionHasErrors('email');

        $this->assertSame($patient->email, $patient->fresh()->email);
        $this->assertSame($patient->name, $patient->fresh()->name);
        $this->assertSame($otherPatient->email, $otherPatient->fresh()->email);
    }

    #[DataProvider('invalidProfileFields')]
    public function test_profile_validation_displays_errors_and_leaves_data_unchanged(string $field, mixed $value, string $error): void
    {
        $patient = User::factory()->create();
        $originalAttributes = $patient->fresh()->getAttributes();
        $this->actingAs($patient)->followingRedirects()->from(route('patient.profile'))
            ->patch(route('patient.profile.update'), ['name' => $patient->name, 'email' => $patient->email, $field => $value])
            ->assertSeeText($error);

        $this->assertSame($originalAttributes, $patient->fresh()->getAttributes());
    }

    /** @return array<string, array{string, mixed, string}> */
    public static function invalidProfileFields(): array
    {
        return [
            'required name' => ['name', '', 'The name field is required.'],
            'required email' => ['email', '', 'The email field is required.'],
            'array name' => ['name', ['invalid'], 'The name field must be a string.'],
            'array email' => ['email', ['invalid'], 'The email field must be a string.'],
            'array phone' => ['phone', ['invalid'], 'The phone field must be a string.'],
            'array address' => ['address', ['invalid'], 'The address field must be a string.'],
            'invalid email' => ['email', 'invalid', 'The email field must be a valid email address.'],
            'long name' => ['name', str_repeat('a', 256), 'The name field must not be greater than 255 characters.'],
            'long email' => ['email', str_repeat('a', 256).'@example.test', 'The email field must not be greater than 255 characters.'],
            'long phone' => ['phone', str_repeat('1', 31), 'The phone field must not be greater than 30 characters.'],
            'long address' => ['address', str_repeat('a', 1001), 'The address field must not be greater than 1000 characters.'],
            'invalid date' => ['date_of_birth', 'invalid', 'The date of birth field must match the format Y-m-d.'],
            'future date' => ['date_of_birth', '2999-01-01', 'The date of birth field must be a date before today.'],
        ];
    }

    public function test_messages_are_owned_by_account_not_by_email_or_query_parameters(): void
    {
        $patient = User::factory()->create();
        $otherPatient = User::factory()->create();
        ContactMessage::factory()->for($patient, 'patient')->create(['message' => 'My account message', 'email' => 'old@example.test']);
        ContactMessage::factory()->for($otherPatient, 'patient')->create(['message' => 'Private other message', 'email' => $patient->email]);
        ContactMessage::factory()->create(['message' => 'Unverified public message', 'email' => $patient->email]);

        $this->actingAs($patient)->get(route('patient.messages.index', ['patient_id' => $otherPatient->id, 'email' => $patient->email]))
            ->assertSeeText('My account message')
            ->assertDontSeeText(['Private other message', 'Unverified public message']);
    }

    public function test_patient_can_send_a_message_with_server_controlled_ownership_and_identity(): void
    {
        $patient = User::factory()->create();
        $otherPatient = User::factory()->create();

        $this->actingAs($patient)->followingRedirects()->post(route('patient.messages.store'), [
            'subject' => 'Visit question', 'message' => 'Please help arrange my visit.',
            'patient_id' => $otherPatient->id, 'name' => 'Forged sender', 'email' => $otherPatient->email, 'status' => 'read',
        ])->assertSeeText(['Your message has been sent to our care team.', 'Please help arrange my visit.']);

        $this->assertDatabaseCount('contact_messages', 1);
        $this->assertDatabaseHas('contact_messages', ['patient_id' => $patient->id, 'name' => $patient->name,
            'email' => $patient->email, 'phone' => $patient->phone, 'subject' => 'Visit question',
            'message' => 'Please help arrange my visit.', 'status' => 'unread']);
    }

    public function test_message_subject_is_optional(): void
    {
        $patient = User::factory()->create();
        $this->actingAs($patient)->post(route('patient.messages.store'), ['message' => 'A question without a subject.'])
            ->assertRedirect(route('patient.messages.index'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('contact_messages', ['patient_id' => $patient->id, 'subject' => null, 'message' => 'A question without a subject.']);
    }

    #[DataProvider('invalidMessages')]
    public function test_invalid_messages_show_feedback_and_are_not_saved(array $payload, string $error): void
    {
        $this->actingAs(User::factory()->create())->followingRedirects()->from(route('patient.messages.index'))
            ->post(route('patient.messages.store'), $payload)->assertSeeText($error);
        $this->assertDatabaseEmpty('contact_messages');
    }

    /** @return array<string, array{array<string, mixed>, string}> */
    public static function invalidMessages(): array
    {
        return [
            'required message' => [[], 'The message field is required.'],
            'array message' => [['message' => ['invalid']], 'The message field must be a string.'],
            'long message' => [['message' => str_repeat('a', 5001)], 'The message field must not be greater than 5000 characters.'],
            'array subject' => [['subject' => ['invalid'], 'message' => 'Valid message'], 'The subject field must be a string.'],
            'long subject' => [['subject' => str_repeat('a', 256), 'message' => 'Valid message'], 'The subject field must not be greater than 255 characters.'],
        ];
    }

    public function test_patient_content_is_escaped_in_dashboard_details_profile_and_messages(): void
    {
        $unsafeText = '<script>alert("unsafe")</script>';
        $patient = User::factory()->create(['name' => $unsafeText, 'address' => $unsafeText]);
        $appointment = Appointment::factory()->for($patient, 'patient')->create(['reason' => $unsafeText]);
        ContactMessage::factory()->for($patient, 'patient')->create(['subject' => $unsafeText, 'message' => $unsafeText]);
        $this->actingAs($patient);

        foreach ([route('patient.dashboard'), route('patient.profile'), route('patient.messages.index'), route('patient.appointments.show', $appointment)] as $url) {
            $this->get($url)->assertSee($unsafeText)->assertDontSee($unsafeText, false);
        }
    }

    public function test_patient_forms_require_csrf_tokens(): void
    {
        $patient = User::factory()->create();
        $this->app->instance('env', 'local');
        $this->actingAs($patient)->post(route('patient.messages.store'), ['message' => 'No token'])->assertStatus(419);
        $this->patch(route('patient.profile.update'), ['name' => 'No token', 'email' => $patient->email])->assertStatus(419);
        $this->assertDatabaseEmpty('contact_messages');
        $this->assertSame($patient->name, $patient->fresh()->name);
    }

    /** @return array<int, array{string, string}> */
    private function patientEndpoints(): array
    {
        return [
            ['GET', '/patient'], ['GET', '/patient/appointments'], ['GET', '/patient/appointments/999999'],
            ['GET', '/patient/profile'], ['PATCH', '/patient/profile'],
            ['GET', '/patient/messages'], ['POST', '/patient/messages'],
        ];
    }
}
