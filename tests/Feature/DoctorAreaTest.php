<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class DoctorAreaTest extends TestCase
{
    use RefreshDatabase;

    #[TestWith([null])]
    #[TestWith(['patient'])]
    #[TestWith(['admin'])]
    public function test_every_doctor_endpoint_requires_the_doctor_role(?string $role): void
    {
        if ($role !== null) {
            $this->actingAs(User::factory()->create(['role' => $role]));
        }

        foreach (['/doctor', '/doctor/appointments', '/doctor/appointments/1', '/doctor/patients', '/doctor/patients/1', '/doctor/schedule', '/doctor/schedule/1/edit', '/doctor/profile'] as $url) {
            $response = $this->get($url);
            $role === null ? $response->assertRedirect(route('login')) : $response->assertForbidden();
        }

        foreach ([['patch', '/doctor/appointments/1'], ['post', '/doctor/schedule'], ['patch', '/doctor/schedule/1'], ['delete', '/doctor/schedule/1'], ['patch', '/doctor/profile']] as [$method, $url]) {
            $response = $this->{$method}($url);
            $role === null ? $response->assertRedirect(route('login')) : $response->assertForbidden();
        }
    }

    public function test_dashboard_and_patient_history_are_scoped_to_the_current_doctor(): void
    {
        $this->travelTo(Carbon::parse('2026-09-14 08:00'));
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();
        foreach (['pending', 'confirmed', 'completed', 'cancelled', 'rejected'] as $index => $status) {
            Appointment::factory()->create(['doctor_id' => $doctor->id, 'patient_id' => $patient->id, 'status' => $status, 'appointment_date' => '2026-09-14', 'appointment_time' => sprintf('%02d:00', 9 + $index)]);
        }
        $foreign = Appointment::factory()->create(['patient_id' => $patient->id, 'notes' => 'Foreign confidential notes']);
        $unrelated = User::factory()->create();

        $this->actingAs($doctor->user)->get(route('doctor.dashboard'))->assertOk()
            ->assertViewHas('patientCount', 1)
            ->assertViewHas('todayAppointments', fn ($visits): bool => $visits->count() === 5 && $visits->every(fn ($visit): bool => $visit->doctor_id === $doctor->id))
            ->assertViewHas('statusCounts', fn ($counts): bool => $counts->all() === ['pending' => 1, 'confirmed' => 1, 'completed' => 1, 'cancelled' => 1, 'rejected' => 1]);
        $this->get(route('doctor.appointments.index'))->assertOk()->assertViewHas('appointments', fn ($visits): bool => $visits->total() === 5);
        $this->get(route('doctor.patients.index'))->assertOk()->assertViewHas('patients', fn ($patients): bool => $patients->total() === 1 && $patients->first()->doctor_appointments_count === 5);
        $this->get(route('doctor.patients.show', $patient))->assertOk()->assertDontSeeText($foreign->notes)
            ->assertViewHas('appointments', fn ($visits): bool => $visits->total() === 5);
        $this->get(route('doctor.patients.show', $unrelated))->assertNotFound();
        $this->get(route('doctor.appointments.show', $foreign))->assertNotFound();
        $this->patchJson(route('doctor.appointments.update', $foreign), ['action' => 'accept', 'notes' => 'Tampered'])->assertNotFound();
        $this->assertSame('pending', $foreign->fresh()->status);
        $this->assertSame('Foreign confidential notes', $foreign->fresh()->notes);
    }

    /** @return array<string, array{string, string, ?string}> */
    public static function transitions(): array
    {
        $cases = [];
        foreach (['pending', 'confirmed', 'completed', 'cancelled', 'rejected'] as $status) {
            foreach (['accept', 'reject', 'complete', 'notes'] as $action) {
                $next = match (true) {
                    $status === 'pending' && $action === 'accept' => 'confirmed',
                    $status === 'pending' && $action === 'reject' => 'rejected',
                    $status === 'confirmed' && $action === 'complete' => 'completed',
                    in_array($status, ['pending', 'confirmed', 'completed'], true) && $action === 'notes' => $status,
                    default => null,
                };
                $cases[$status.' '.$action] = [$status, $action, $next];
            }
        }

        return $cases;
    }

    #[DataProvider('transitions')]
    public function test_all_status_transitions_and_notes_are_enforced(string $status, string $action, ?string $next): void
    {
        $visit = Appointment::factory()->create(['status' => $status, 'notes' => 'Original notes']);
        $this->actingAs($visit->doctor->user)->get(route('doctor.appointments.show', $visit))->assertOk()->assertSeeText(ucfirst($status));
        $response = $this->patchJson(route('doctor.appointments.update', $visit), [
            'action' => $action, 'notes' => 'Updated notes', 'doctor_id' => 99999, 'patient_id' => 99999, 'status' => 'cancelled',
        ]);
        if ($next === null) {
            $response->assertUnprocessable()->assertJsonValidationErrors('action');
        } else {
            $response->assertRedirect(route('doctor.appointments.show', $visit))->assertSessionHas('status', 'Appointment updated successfully.');
        }
        $this->assertSame($next ?? $status, $visit->fresh()->status);
        $this->assertSame($next === null ? 'Original notes' : 'Updated notes', $visit->fresh()->notes);
        $this->assertSame($visit->doctor_id, $visit->fresh()->doctor_id);
        $this->assertSame($visit->patient_id, $visit->fresh()->patient_id);
    }

    public function test_notes_validation_and_policy_denial_do_not_modify_appointments(): void
    {
        $visit = Appointment::factory()->create(['notes' => 'Private clinical note']);
        $this->actingAs($visit->doctor->user);
        $this->patchJson(route('doctor.appointments.update', $visit), ['action' => 'accept', 'notes' => str_repeat('a', 5001)])->assertUnprocessable()->assertJsonValidationErrors('notes');
        $this->patchJson(route('doctor.appointments.update', $visit), ['action' => ['accept']])->assertUnprocessable()->assertJsonValidationErrors('action');
        $this->assertSame('pending', $visit->fresh()->status);
        Gate::before(fn (User $user, string $ability): ?bool => $ability === 'manage' ? false : null);
        $this->patchJson(route('doctor.appointments.update', $visit), ['action' => 'accept'])->assertForbidden();
        $this->assertSame('pending', $visit->fresh()->status);
        $this->actingAs($visit->patient)->get(route('patient.appointments.show', $visit))->assertOk()->assertDontSeeText('Private clinical note');
    }

    public function test_notes_are_preserved_when_omitted_and_repeated_actions_are_rejected(): void
    {
        $visit = Appointment::factory()->create(['notes' => 'Keep this note']);
        $this->actingAs($visit->doctor->user)->patch(route('doctor.appointments.update', $visit), ['action' => 'accept'])->assertRedirect();
        $this->patchJson(route('doctor.appointments.update', $visit), ['action' => 'accept'])->assertUnprocessable();
        $this->assertSame('confirmed', $visit->fresh()->status);
        $this->assertSame('Keep this note', $visit->fresh()->notes);
    }

    public function test_profile_updates_only_authorized_fields_and_clears_email_verification(): void
    {
        $doctor = Doctor::factory()->create();
        $other = Doctor::factory()->create();
        $original = $other->fresh()->getAttributes();
        $this->actingAs($doctor->user)->get(route('doctor.profile', ['doctor_id' => $other->id]))->assertOk()->assertSee($doctor->specialization);
        $this->patch(route('doctor.profile.update'), [
            'name' => 'Updated Doctor', 'email' => 'updated@example.test', 'phone' => '123456',
            'specialization' => 'Cardiology', 'experience' => 15, 'education' => 'Medical degree', 'bio' => 'Professional biography',
            'id' => $other->id, 'user_id' => $other->user_id, 'department_id' => $other->department_id, 'role' => 'admin',
        ])->assertRedirect(route('doctor.profile'))->assertSessionHas('status');
        $this->assertSame('Updated Doctor', $doctor->user->fresh()->name);
        $this->assertNull($doctor->user->fresh()->email_verified_at);
        $this->assertSame('doctor', $doctor->user->fresh()->role);
        $this->assertSame('Cardiology', $doctor->fresh()->specialization);
        $this->assertSame($doctor->department_id, $doctor->fresh()->department_id);
        $this->assertSame($doctor->user_id, $doctor->fresh()->user_id);
        $this->assertSame($original, $other->fresh()->getAttributes());
    }

    public function test_invalid_profile_data_does_not_partially_update_the_account(): void
    {
        $doctor = Doctor::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($doctor->user)->patchJson(route('doctor.profile.update'), [
            'name' => '', 'email' => $other->email, 'phone' => [], 'specialization' => '',
            'experience' => -1, 'education' => str_repeat('a', 256), 'bio' => str_repeat('a', 5001),
        ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'phone', 'specialization', 'experience', 'education', 'bio']);
        $this->assertSame($doctor->user->email, $doctor->user->fresh()->email);
    }

    public function test_doctor_without_a_profile_cannot_enter_the_area(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'doctor']));
        foreach (['doctor.dashboard', 'doctor.appointments.index', 'doctor.patients.index', 'doctor.schedule.index', 'doctor.profile'] as $route) {
            $this->get(route($route))->assertNotFound();
        }
    }

    public function test_empty_dashboard_and_lists_render_and_profile_can_keep_its_email(): void
    {
        $doctor = Doctor::factory()->create();
        $verifiedAt = $doctor->user->email_verified_at;
        $this->actingAs($doctor->user)->get(route('doctor.dashboard'))->assertOk()->assertSeeText('No appointments to display.')->assertViewHas('patientCount', 0)
            ->assertSeeText(['Appointments', 'Patients', 'Schedule', 'Profile', 'Logout'])
            ->assertDontSee('Thoughtful, evidence-based care for our community');
        $this->get(route('doctor.appointments.index'))->assertOk()->assertSeeText('No appointments to display.');
        $this->get(route('doctor.patients.index'))->assertOk()->assertSeeText('No patients are assigned to you yet.');
        $this->get(route('doctor.schedule.index'))->assertOk()->assertSeeText('No working hours have been added yet.');
        $this->patch(route('doctor.profile.update'), [
            'name' => $doctor->user->name, 'email' => $doctor->user->email,
            'specialization' => $doctor->specialization, 'experience' => 0,
        ])->assertRedirect(route('doctor.profile'));
        $this->assertTrue($verifiedAt->equalTo($doctor->user->fresh()->email_verified_at));
    }

    public function test_doctor_mutations_require_csrf_tokens(): void
    {
        $visit = Appointment::factory()->create();
        $this->actingAs($visit->doctor->user);
        $this->app->instance('env', 'local');
        $this->patch(route('doctor.appointments.update', $visit), ['action' => 'accept'])->assertStatus(419);
        $this->patch(route('doctor.profile.update'))->assertStatus(419);
        $this->post(route('doctor.schedule.store'))->assertStatus(419);
        $this->patch(route('doctor.schedule.update', 1))->assertStatus(419);
        $this->delete(route('doctor.schedule.destroy', 1))->assertStatus(419);
        $this->assertSame('pending', $visit->fresh()->status);
    }
}
