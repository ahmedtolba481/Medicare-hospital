<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\ContactMessage;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class AdminCrudHardeningTest extends TestCase
{
    use RefreshDatabase;

    #[TestWith(['patient'])]
    #[TestWith(['admin'])]
    public function test_doctor_edit_cannot_modify_a_mislinked_account(string $role): void
    {
        $account = User::factory()->create(['role' => $role]);
        $doctor = Doctor::factory()->create(['user_id' => $account->id]);
        $original = $account->fresh()->getAttributes();
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->putJson(route('admin.doctors.update', $doctor), $this->doctorData($doctor))
            ->assertUnprocessable()->assertJsonValidationErrors('doctor');
        $this->assertSame($original, $account->fresh()->getAttributes());
        $this->assertSame($doctor->specialization, $doctor->fresh()->specialization);
    }

    #[TestWith(['name', '   '])]
    #[TestWith(['email', 'invalid-email'])]
    #[TestWith(['department_id', 999999])]
    #[TestWith(['department_id', ['1']])]
    #[TestWith(['experience', -1])]
    #[TestWith(['experience', 81])]
    #[TestWith(['experience', 1.5])]
    #[TestWith(['experience', 'many'])]
    #[TestWith(['experience', null])]
    #[TestWith(['specialization', ''])]
    public function test_invalid_doctor_edits_leave_account_and_profile_unchanged(string $field, mixed $value): void
    {
        $doctor = Doctor::factory()->create();
        $account = $doctor->user->fresh()->getAttributes();
        $profile = $doctor->fresh()->getAttributes();
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->putJson(route('admin.doctors.update', $doctor), [...$this->doctorData($doctor), $field => $value])
            ->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertSame($account, $doctor->user->fresh()->getAttributes());
        $this->assertSame($profile, $doctor->fresh()->getAttributes());
    }

    public function test_unsupported_image_uploads_cannot_replace_images_or_write_files(): void
    {
        Storage::fake('public');
        $doctor = Doctor::factory()->create(['image' => 'existing.jpg']);
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put(route('admin.doctors.update', $doctor), [
                ...$this->doctorData($doctor), 'image' => UploadedFile::fake()->create('payload.php', 1, 'application/x-httpd-php'),
                'role' => 'admin', 'user_id' => 99999,
            ])->assertRedirect(route('admin.doctors.index'));
        $this->assertSame('existing.jpg', $doctor->fresh()->image);
        $this->assertSame($doctor->user_id, $doctor->fresh()->user_id);
        $this->assertSame('doctor', $doctor->user->fresh()->role);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    #[TestWith(['pending'])]
    #[TestWith(['confirmed'])]
    #[TestWith(['completed'])]
    #[TestWith(['cancelled'])]
    #[TestWith(['rejected'])]
    public function test_admin_cannot_mutate_patients_or_appointment_statuses_through_read_only_pages(string $status): void
    {
        $visit = Appointment::factory()->create(['status' => $status]);
        $appointment = $visit->fresh()->getAttributes();
        $patient = $visit->patient->fresh()->getAttributes();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach (['post', 'put', 'patch', 'delete'] as $method) {
            $this->{$method}(route('admin.appointments.show', $visit), ['status' => 'completed', 'doctor_id' => 99999])->assertStatus(405);
            $this->{$method}(route('admin.patients.show', $visit->patient), ['name' => 'Forged', 'role' => 'admin'])->assertStatus(405);
        }
        $this->patchJson(route('doctor.appointments.update', $visit), ['action' => 'complete'])->assertForbidden();
        $this->patchJson(route('patient.appointments.cancel', $visit))->assertForbidden();
        $this->assertSame($appointment, $visit->fresh()->getAttributes());
        $this->assertSame($patient, $visit->patient->fresh()->getAttributes());
    }

    #[TestWith([null])]
    #[TestWith(['archived'])]
    #[TestWith([['read']])]
    #[TestWith([1])]
    public function test_invalid_message_states_preserve_the_entire_message(mixed $status): void
    {
        $message = ContactMessage::factory()->create();
        $original = $message->fresh()->getAttributes();
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->patchJson(route('admin.messages.update', $message), ['status' => $status, 'message' => 'Forged'])
            ->assertUnprocessable()->assertJsonValidationErrors('status');
        $this->assertSame($original, $message->fresh()->getAttributes());
    }

    public function test_department_duplicate_after_validation_returns_feedback_and_preserves_the_competing_record(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        Event::listen('eloquent.creating: '.Department::class, function (Department $department): void {
            DB::table('departments')->insert(['name' => $department->name, 'description' => 'Competing record']);
        });

        $this->postJson(route('admin.departments.store'), ['name' => 'Concurrent department', 'description' => 'Requested record'])
            ->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->assertDatabaseCount('departments', 1);
        $this->assertDatabaseHas('departments', ['name' => 'Concurrent department', 'description' => 'Competing record']);
    }

    public function test_department_duplicate_after_edit_validation_preserves_original_data(): void
    {
        $department = Department::factory()->create();
        $original = $department->fresh()->getAttributes();
        Event::listen('eloquent.updating: '.Department::class, function (Department $department): void {
            DB::table('departments')->insert(['name' => $department->name]);
        });
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->putJson(route('admin.departments.update', $department), ['name' => 'Contested name', 'description' => 'Attempted edit'])
            ->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->assertSame($original, $department->fresh()->getAttributes());
    }

    public function test_department_names_cannot_duplicate_another_record_on_edit(): void
    {
        $department = Department::factory()->create(['name' => 'Cardiology']);
        $other = Department::factory()->create(['name' => 'Neurology']);
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->putJson(route('admin.departments.update', $other), ['name' => '  Cardiology  '])
            ->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->assertSame('Neurology', $other->fresh()->name);
        $this->assertModelExists($department);
    }

    /** @return array<string, mixed> */
    private function doctorData(Doctor $doctor): array
    {
        return [
            'name' => 'Updated Doctor', 'email' => 'updated-doctor@example.test',
            'department_id' => $doctor->department_id, 'specialization' => 'Updated specialty',
            'experience' => 10, 'education' => 'Medical degree', 'bio' => 'Updated biography',
        ];
    }
}
