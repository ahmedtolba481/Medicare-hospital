<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class AdminDoctorTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_adds_a_doctor_login_and_public_profile_with_a_fixed_role(): void
    {
        $department = Department::factory()->create();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post(route('admin.doctors.store'), [...$this->data($department), 'password' => 'SafePassword123', 'password_confirmation' => 'SafePassword123', 'role' => 'admin', 'user_id' => 99999])
            ->assertRedirect(route('admin.doctors.index'))->assertSessionHas('status', 'Doctor created successfully.');
        $doctor = Doctor::with('user')->sole();
        $this->assertSame('doctor', $doctor->user->role);
        $this->assertTrue(Hash::check('SafePassword123', $doctor->user->password));
        $this->assertSame($department->id, $doctor->department_id);
        $this->get(route('doctors.show', $doctor))->assertOk()->assertSeeText(['New Doctor', 'Cardiology', 'Medical degree']);
        $this->get(route('admin.doctors.index'))->assertOk()->assertSeeText(['New Doctor', $department->name]);
        $this->actingAs($doctor->user)->get(route('doctor.dashboard'))->assertOk();
    }

    public function test_edit_updates_account_and_profile_without_changing_identity_or_password(): void
    {
        $doctor = Doctor::factory()->create();
        $department = Department::factory()->create();
        $password = $doctor->user->password;
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.doctors.edit', $doctor))->assertOk()->assertSee($doctor->user->email);
        $this->put(route('admin.doctors.update', $doctor), [...$this->data($department), 'user_id' => 99999, 'id' => 99999, 'role' => 'admin'])
            ->assertRedirect(route('admin.doctors.index'))->assertSessionHas('status', 'Doctor updated successfully.');
        $this->assertSame($doctor->user_id, $doctor->fresh()->user_id);
        $this->assertSame('doctor', $doctor->user->fresh()->role);
        $this->assertSame($password, $doctor->user->fresh()->password);
        $this->assertNull($doctor->user->fresh()->email_verified_at);
        $this->assertSame($department->id, $doctor->fresh()->department_id);
        $this->assertSame('Cardiology', $doctor->fresh()->specialization);
        $this->assertSame('New Doctor', $doctor->user->fresh()->name);
    }

    public function test_unchanged_email_is_valid_and_password_changes_are_rejected(): void
    {
        $doctor = Doctor::factory()->create();
        $data = [...$this->data($doctor->department), 'email' => $doctor->user->email];
        $verifiedAt = $doctor->user->email_verified_at;
        $this->actingAs(User::factory()->create(['role' => 'admin']))->put(route('admin.doctors.update', $doctor), $data)->assertRedirect();
        $this->assertTrue($verifiedAt->equalTo($doctor->user->fresh()->email_verified_at));
        $this->putJson(route('admin.doctors.update', $doctor), [...$data, 'password' => 'Changed123'])->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    public function test_invalid_doctor_creation_does_not_create_an_orphan_account(): void
    {
        $existing = User::factory()->create();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->postJson(route('admin.doctors.store'), [])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'password', 'department_id', 'specialization', 'experience']);
        $this->postJson(route('admin.doctors.store'), [
            'name' => [], 'email' => $existing->email, 'password' => 'short', 'password_confirmation' => 'different',
            'phone' => str_repeat('1', 31), 'department_id' => 99999, 'specialization' => str_repeat('s', 256),
            'experience' => -1, 'education' => str_repeat('e', 256), 'bio' => str_repeat('b', 5001),
        ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'password', 'phone', 'department_id', 'specialization', 'experience', 'education', 'bio']);
        $this->assertDatabaseCount('doctors', 0);
        $this->assertDatabaseCount('users', 2);
    }

    public function test_duplicate_email_on_edit_preserves_both_records(): void
    {
        $doctor = Doctor::factory()->create();
        $other = User::factory()->create();
        $original = $doctor->fresh()->getAttributes();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->putJson(route('admin.doctors.update', $doctor), [...$this->data($doctor->department), 'email' => $other->email])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertSame($original, $doctor->fresh()->getAttributes());
        $this->assertSame($doctor->user->email, $doctor->user->fresh()->email);
    }

    public function test_delete_removes_only_an_unused_doctor_account_and_its_schedule(): void
    {
        $schedule = DoctorSchedule::factory()->create();
        $doctor = $schedule->doctor;
        $user = $doctor->user;
        $department = $doctor->department;
        $other = Doctor::factory()->create();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->delete(route('admin.doctors.destroy', $doctor))
            ->assertRedirect(route('admin.doctors.index'))->assertSessionHas('status');
        $this->assertModelMissing($doctor);
        $this->assertModelMissing($user);
        $this->assertModelMissing($schedule);
        $this->assertModelExists($other);
        $this->assertModelExists($department);
    }

    #[TestWith(['pending'])]
    #[TestWith(['confirmed'])]
    #[TestWith(['completed'])]
    #[TestWith(['cancelled'])]
    #[TestWith(['rejected'])]
    public function test_doctors_with_any_appointment_history_cannot_be_deleted(string $status): void
    {
        $visit = Appointment::factory()->create(['status' => $status]);
        $this->actingAs(User::factory()->create(['role' => 'admin']))->from(route('admin.doctors.index'))
            ->delete(route('admin.doctors.destroy', $visit->doctor))->assertRedirect(route('admin.doctors.index'))->assertSessionHasErrors('doctor');
        $this->assertModelExists($visit->doctor);
        $this->assertModelExists($visit->doctor->user);
        $this->assertModelExists($visit);
    }

    public function test_deletion_preserves_a_doctors_history_as_a_patient(): void
    {
        $doctor = Doctor::factory()->create();
        $visit = Appointment::factory()->create(['patient_id' => $doctor->user_id]);
        $this->actingAs(User::factory()->create(['role' => 'admin']))->deleteJson(route('admin.doctors.destroy', $doctor))
            ->assertUnprocessable()->assertJsonValidationErrors('doctor');
        $this->assertModelExists($doctor);
        $this->assertModelExists($visit);
    }

    public function test_a_profile_linked_to_a_non_doctor_account_cannot_delete_that_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $doctor = Doctor::factory()->create(['user_id' => $admin->id]);
        $this->actingAs($admin)->deleteJson(route('admin.doctors.destroy', $doctor))->assertUnprocessable()->assertJsonValidationErrors('doctor');
        $this->assertModelExists($admin);
        $this->assertModelExists($doctor);
    }

    public function test_invalid_form_feedback_preserves_safe_values_without_echoing_passwords(): void
    {
        $department = Department::factory()->create();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->followingRedirects()->from(route('admin.doctors.create'))->post(route('admin.doctors.store'), [
            ...$this->data($department), 'name' => '<script>alert(1)</script>',
            'experience' => -1, 'password' => 'NeverEchoThis123', 'password_confirmation' => 'NeverEchoThis123',
        ])->assertOk()->assertSeeText('Please review the following:')->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('NeverEchoThis123');
        $this->assertDatabaseCount('doctors', 0);
    }

    /** @return array<string, mixed> */
    private function data(Department $department): array
    {
        return ['name' => 'New Doctor', 'email' => 'newdoctor@example.test', 'phone' => '123456', 'department_id' => $department->id, 'specialization' => 'Cardiology', 'experience' => 12, 'education' => 'Medical degree', 'bio' => 'Doctor biography'];
    }
}
