<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class AppointmentManagementTest extends TestCase
{
    use RefreshDatabase;

    #[TestWith(['pending', 'status-pending', 'awaiting confirmation'])]
    #[TestWith(['confirmed', 'status-confirmed', 'confirmed by the care team'])]
    #[TestWith(['completed', 'status-completed', 'has been completed'])]
    #[TestWith(['cancelled', 'status-cancelled', 'has been cancelled'])]
    #[TestWith(['rejected', 'status-rejected', 'was not accepted'])]
    public function test_every_status_has_a_clear_badge_and_explanation(string $status, string $badge, string $message): void
    {
        $appointment = $this->futureAppointment($status);
        $this->actingAs($appointment->patient);

        $this->get(route('patient.appointments.index'))->assertSeeText(ucfirst($status))->assertSee($badge);
        $this->get(route('patient.appointments.show', $appointment))
            ->assertSeeText([$appointment->doctor->user->name, $appointment->doctor->department->name,
                $appointment->doctor->specialization, 'Sep 15, 2026', '9:00 AM', 'Consultation', ucfirst($status), $message])
            ->assertSee($badge)
            ->assertSee('href="'.route('doctors.show', $appointment->doctor).'"', false);
    }

    #[TestWith(['pending'])]
    #[TestWith(['confirmed'])]
    public function test_patient_can_cancel_an_eligible_appointment_and_sees_confirmation(string $status): void
    {
        $appointment = $this->futureAppointment($status);
        $this->actingAs($appointment->patient)->get(route('patient.appointments.show', $appointment))
            ->assertSeeText('Confirm cancellation')
            ->assertSee('name="_token"', false);

        $this->followingRedirects()->patch(route('patient.appointments.cancel', $appointment), [
            'status' => 'completed', 'patient_id' => 999999, 'doctor_id' => 999999, 'reason' => 'Forged reason',
        ])->assertSeeText(['Your appointment has been cancelled.', 'Cancelled'])
            ->assertDontSeeText('Confirm cancellation');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id, 'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id, 'status' => 'cancelled', 'reason' => 'Consultation',
            'appointment_date' => '2026-09-15', 'appointment_time' => '09:00:00',
        ]);
    }

    #[TestWith(['completed'])]
    #[TestWith(['cancelled'])]
    #[TestWith(['rejected'])]
    public function test_terminal_statuses_cannot_be_cancelled(string $status): void
    {
        $appointment = $this->futureAppointment($status);
        $this->actingAs($appointment->patient)->get(route('patient.appointments.show', $appointment))
            ->assertDontSeeText('Confirm cancellation');

        $this->followingRedirects()->patch(route('patient.appointments.cancel', $appointment))
            ->assertSeeText('Only future pending or confirmed appointments can be cancelled.');

        $this->assertSame($status, $appointment->fresh()->status);
    }

    #[TestWith(['pending', '2026-09-13', '09:00:00'])]
    #[TestWith(['confirmed', '2026-09-14', '07:59:00'])]
    #[TestWith(['pending', '2026-09-14', '08:00:00'])]
    public function test_appointments_that_have_started_cannot_be_cancelled(string $status, string $date, string $time): void
    {
        $appointment = $this->futureAppointment($status);
        $appointment->update(['appointment_date' => $date, 'appointment_time' => $time]);

        $this->actingAs($appointment->patient)->get(route('patient.appointments.show', $appointment))
            ->assertDontSeeText('Confirm cancellation');
        $this->patchJson(route('patient.appointments.cancel', $appointment))
            ->assertUnprocessable()->assertJsonValidationErrors('appointment');
        $this->assertSame($status, $appointment->fresh()->status);
    }

    public function test_stale_and_repeated_cancellations_cannot_overwrite_status(): void
    {
        $appointment = $this->futureAppointment('confirmed');
        $this->actingAs($appointment->patient)->get(route('patient.appointments.show', $appointment))
            ->assertSeeText('Confirm cancellation');
        $appointment->update(['status' => 'completed']);

        $this->patchJson(route('patient.appointments.cancel', $appointment))
            ->assertUnprocessable()->assertJsonValidationErrors('appointment');
        $this->assertSame('completed', $appointment->fresh()->status);
    }

    public function test_second_cancellation_is_rejected_without_another_update(): void
    {
        $appointment = $this->futureAppointment('pending');
        $this->actingAs($appointment->patient)->patch(route('patient.appointments.cancel', $appointment))->assertSessionHasNoErrors();
        $updatedAt = $appointment->fresh()->updated_at->toDateTimeString();
        $this->travel(1)->minutes();

        $this->patchJson(route('patient.appointments.cancel', $appointment))->assertUnprocessable();
        $this->assertSame('cancelled', $appointment->fresh()->status);
        $this->assertSame($updatedAt, $appointment->fresh()->updated_at->toDateTimeString());
    }

    public function test_another_patient_cannot_view_or_cancel_the_appointment(): void
    {
        $appointment = $this->futureAppointment('pending');
        $this->actingAs(User::factory()->create());
        $this->get(route('patient.appointments.show', $appointment))->assertNotFound();
        $this->patch(route('patient.appointments.cancel', $appointment), ['patient_id' => $appointment->patient_id])->assertNotFound();
        $this->patch(route('patient.appointments.cancel', 999999))->assertNotFound();
        $this->assertSame('pending', $appointment->fresh()->status);
    }

    #[TestWith(['patient', true, true])]
    #[TestWith(['patient', false, false])]
    #[TestWith(['doctor', true, false])]
    #[TestWith(['doctor', false, false])]
    #[TestWith(['admin', true, false])]
    #[TestWith(['admin', false, false])]
    public function test_cancellation_policy_enforces_role_and_ownership(string $role, bool $ownsAppointment, bool $allowed): void
    {
        $user = User::factory()->create(['role' => $role]);
        $owner = $ownsAppointment ? $user : User::factory()->create();
        $appointment = Appointment::factory()->for($owner, 'patient')->create();

        $this->assertSame($allowed, Gate::forUser($user)->allows('cancel', $appointment));
    }

    public function test_cancel_endpoint_obeys_an_authorization_denial(): void
    {
        $appointment = $this->futureAppointment('pending');
        Gate::before(fn (User $user, string $ability): ?bool => $ability === 'cancel' ? false : null);

        $this->actingAs($appointment->patient)->patch(route('patient.appointments.cancel', $appointment))->assertForbidden();
        $this->assertSame('pending', $appointment->fresh()->status);
    }

    public function test_guests_cannot_cancel(): void
    {
        $appointment = $this->futureAppointment('pending');
        $this->patch(route('patient.appointments.cancel', $appointment))->assertRedirect(route('login'));
        $this->assertSame('pending', $appointment->fresh()->status);
    }

    #[TestWith(['doctor'])]
    #[TestWith(['admin'])]
    public function test_non_patient_roles_cannot_cancel(string $role): void
    {
        $appointment = $this->futureAppointment('confirmed');
        $this->actingAs(User::factory()->create(['role' => $role]))
            ->patch(route('patient.appointments.cancel', $appointment))->assertForbidden();
        $this->assertSame('confirmed', $appointment->fresh()->status);
    }

    public function test_cancel_requires_csrf_and_cannot_be_triggered_by_a_get_request(): void
    {
        $appointment = $this->futureAppointment('pending');
        $this->app->instance('env', 'local');
        $this->actingAs($appointment->patient)->patch(route('patient.appointments.cancel', $appointment))->assertStatus(419);
        $this->get(route('patient.appointments.cancel', $appointment))->assertStatus(405);
        $this->assertSame('pending', $appointment->fresh()->status);
    }

    public function test_patient_cannot_directly_set_an_appointment_status(): void
    {
        $appointment = $this->futureAppointment('pending');
        $this->actingAs($appointment->patient)->patch(route('patient.appointments.show', $appointment), ['status' => 'completed'])
            ->assertStatus(405);
        $this->assertSame('pending', $appointment->fresh()->status);
    }

    private function futureAppointment(string $status): Appointment
    {
        $this->travelTo(Carbon::parse('2026-09-14 08:00:00'));

        return Appointment::factory()->create([
            'status' => $status, 'appointment_date' => '2026-09-15',
            'appointment_time' => '09:00:00', 'reason' => 'Consultation',
        ]);
    }
}
