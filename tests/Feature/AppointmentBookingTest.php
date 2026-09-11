<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class AppointmentBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_must_sign_in_and_return_to_the_selected_doctor(): void
    {
        $doctor = $this->doctorWithSchedule();
        $url = route('patient.appointments.create', ['doctor_id' => $doctor->id]);

        $this->get($url)->assertRedirect(route('login'))->assertSessionHas('url.intended', $url);
        $this->post(route('patient.appointments.store'), $this->payload($doctor))->assertRedirect(route('login'));
        $this->assertDatabaseEmpty('appointments');
    }

    #[TestWith(['doctor'])]
    #[TestWith(['admin'])]
    public function test_non_patient_roles_cannot_view_or_submit_bookings(string $role): void
    {
        $this->actingAs(User::factory()->create(['role' => $role]));
        $this->get(route('patient.appointments.create'))->assertForbidden();
        $this->post(route('patient.appointments.store'), [])->assertForbidden();
        $this->assertDatabaseEmpty('appointments');
    }

    public function test_form_lists_database_doctors_without_requiring_javascript(): void
    {
        $doctor = $this->doctorWithSchedule();
        $this->actingAs(User::factory()->create())->get(route('patient.appointments.create'))
            ->assertSeeText([$doctor->user->name, $doctor->specialization, 'View available times'])
            ->assertDontSee('name="appointment_time"', false);
    }

    public function test_example_schedule_produces_exactly_six_thirty_minute_slots(): void
    {
        $doctor = $this->doctorWithSchedule();
        $this->actingAs(User::factory()->create())->get(route('patient.appointments.create', [
            'doctor_id' => $doctor->id, 'appointment_date' => '2026-09-15',
        ]))->assertViewHas('slots', ['09:00', '09:30', '10:00', '10:30', '11:00', '11:30'])
            ->assertSee('name="_token"', false)->assertSeeText('Submit booking');
    }

    public function test_slots_respect_breaks_overlapping_schedules_and_incomplete_final_periods(): void
    {
        $doctor = $this->doctorWithSchedule();
        $doctor->schedules()->delete();
        DoctorSchedule::factory()->for($doctor)->create(['day' => 'Tuesday', 'start_time' => '09:00:00', 'end_time' => '10:10:00']);
        DoctorSchedule::factory()->for($doctor)->create(['day' => 'Tuesday', 'start_time' => '09:30:00', 'end_time' => '10:00:00']);
        DoctorSchedule::factory()->for($doctor)->create(['day' => 'Tuesday', 'start_time' => '13:00:00', 'end_time' => '14:00:00']);

        $this->actingAs(User::factory()->create())->get(route('patient.appointments.create', [
            'doctor_id' => $doctor->id, 'appointment_date' => '2026-09-15',
        ]))->assertViewHas('slots', ['09:00', '09:30', '13:00', '13:30']);
    }

    #[TestWith(['pending'])]
    #[TestWith(['confirmed'])]
    #[TestWith(['completed'])]
    #[TestWith(['cancelled'])]
    #[TestWith(['rejected'])]
    public function test_existing_bookings_are_excluded_for_every_reserved_status(string $status): void
    {
        $doctor = $this->doctorWithSchedule();
        Appointment::factory()->for($doctor)->create([
            'appointment_date' => '2026-09-15', 'appointment_time' => '09:00:00', 'status' => $status,
        ]);

        $this->actingAs(User::factory()->create())->get(route('patient.appointments.create', [
            'doctor_id' => $doctor->id, 'appointment_date' => '2026-09-15',
        ]))->assertViewHas('slots', ['09:30', '10:00', '10:30', '11:00', '11:30'])
            ->assertDontSee('name="appointment_time" id="slot-0" value="09:00"', false);
    }

    public function test_off_grid_existing_appointments_block_every_overlapping_slot(): void
    {
        $doctor = $this->doctorWithSchedule();
        Appointment::factory()->for($doctor)->create(['appointment_date' => '2026-09-15', 'appointment_time' => '09:15:00']);

        $this->actingAs(User::factory()->create())->get(route('patient.appointments.create', [
            'doctor_id' => $doctor->id, 'appointment_date' => '2026-09-15',
        ]))->assertViewHas('slots', ['10:00', '10:30', '11:00', '11:30']);
    }

    public function test_bookings_for_other_doctors_or_dates_do_not_hide_slots(): void
    {
        $doctor = $this->doctorWithSchedule();
        Appointment::factory()->create(['appointment_date' => '2026-09-15', 'appointment_time' => '09:00:00']);
        Appointment::factory()->for($doctor)->create(['appointment_date' => '2026-09-16', 'appointment_time' => '09:00:00']);

        $this->actingAs(User::factory()->create())->get(route('patient.appointments.create', [
            'doctor_id' => $doctor->id, 'appointment_date' => '2026-09-15',
        ]))->assertViewHas('slots', ['09:00', '09:30', '10:00', '10:30', '11:00', '11:30']);
    }

    public function test_today_excludes_elapsed_times_and_rejects_forged_elapsed_bookings(): void
    {
        $doctor = $this->doctorWithSchedule();
        $this->travelTo(Carbon::parse('2026-09-15 10:10:00'));
        $this->actingAs(User::factory()->create());

        $this->get(route('patient.appointments.create', [
            'doctor_id' => $doctor->id, 'appointment_date' => '2026-09-15',
        ]))->assertViewHas('slots', ['10:30', '11:00', '11:30']);

        $this->postJson(route('patient.appointments.store'), $this->payload($doctor))
            ->assertUnprocessable()->assertJsonValidationErrors('appointment_time');
        $this->assertDatabaseEmpty('appointments');
    }

    public function test_fully_booked_or_non_working_days_have_no_available_times(): void
    {
        $doctor = $this->doctorWithSchedule();
        foreach (['09:00', '09:30', '10:00', '10:30', '11:00', '11:30'] as $time) {
            Appointment::factory()->for($doctor)->create(['appointment_date' => '2026-09-15', 'appointment_time' => $time]);
        }

        $this->actingAs(User::factory()->create());
        foreach (['2026-09-15', '2026-09-16'] as $date) {
            $this->get(route('patient.appointments.create', ['doctor_id' => $doctor->id, 'appointment_date' => $date]))
                ->assertViewHas('slots', [])->assertSeeText('No available times')
                ->assertDontSeeText('Submit booking');
        }
    }

    public function test_valid_booking_is_owned_by_current_patient_pending_and_shows_confirmation(): void
    {
        $doctor = $this->doctorWithSchedule();
        $patient = User::factory()->create();
        $otherPatient = User::factory()->create();

        $this->actingAs($patient)->followingRedirects()->post(route('patient.appointments.store'), [
            ...$this->payload($doctor), 'patient_id' => $otherPatient->id, 'status' => 'confirmed', 'notes' => 'Forged notes',
        ])->assertSeeText(['Your appointment request has been received. Status: pending.', 'Pending', 'Consultation request', $doctor->user->name]);

        $this->assertDatabaseCount('appointments', 1);
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id, 'doctor_id' => $doctor->id, 'appointment_date' => '2026-09-15',
            'appointment_time' => '09:00:00', 'reason' => 'Consultation request', 'status' => 'pending', 'notes' => null,
        ]);
    }

    public function test_a_stale_slot_is_rejected_and_cannot_create_a_duplicate(): void
    {
        $doctor = $this->doctorWithSchedule();
        $patient = User::factory()->create();
        $this->actingAs($patient)->get(route('patient.appointments.create', [
            'doctor_id' => $doctor->id, 'appointment_date' => '2026-09-15',
        ]))->assertViewHas('slots', ['09:00', '09:30', '10:00', '10:30', '11:00', '11:30']);

        Appointment::factory()->for($doctor)->create(['appointment_date' => '2026-09-15', 'appointment_time' => '09:00:00']);
        $this->followingRedirects()->post(route('patient.appointments.store'), $this->payload($doctor))
            ->assertSeeText('This time is no longer available.')
            ->assertSeeText('Consultation request')->assertViewHas('slots', ['09:30', '10:00', '10:30', '11:00', '11:30']);

        $this->assertDatabaseCount('appointments', 1);
        $this->assertDatabaseMissing('appointments', ['patient_id' => $patient->id]);
    }

    public function test_repeated_submissions_create_only_one_booking(): void
    {
        $doctor = $this->doctorWithSchedule();
        $this->actingAs(User::factory()->create())->post(route('patient.appointments.store'), $this->payload($doctor))
            ->assertSessionHasNoErrors();
        $this->postJson(route('patient.appointments.store'), $this->payload($doctor))
            ->assertUnprocessable()->assertJsonValidationErrors('appointment_time');
        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_changed_schedule_is_rechecked_when_booking_is_submitted(): void
    {
        $doctor = $this->doctorWithSchedule();
        $this->actingAs(User::factory()->create())->get(route('patient.appointments.create', [
            'doctor_id' => $doctor->id, 'appointment_date' => '2026-09-15',
        ]))->assertSeeText('09:00');

        $doctor->schedules()->delete();
        $this->postJson(route('patient.appointments.store'), $this->payload($doctor))
            ->assertUnprocessable()->assertJsonValidationErrors('appointment_time');
        $this->assertDatabaseEmpty('appointments');
    }

    #[DataProvider('invalidBookingFields')]
    public function test_backend_rejects_invalid_bookings_without_writing(string $field, mixed $value, string $errorField): void
    {
        $doctor = $this->doctorWithSchedule();
        $this->actingAs(User::factory()->create())->postJson(route('patient.appointments.store'), [
            ...$this->payload($doctor), $field => $value,
        ])->assertUnprocessable()->assertJsonValidationErrors($errorField);
        $this->assertDatabaseEmpty('appointments');
    }

    /** @return array<string, array{string, mixed, string}> */
    public static function invalidBookingFields(): array
    {
        return [
            'missing doctor' => ['doctor_id', null, 'doctor_id'],
            'unknown doctor' => ['doctor_id', 999999, 'doctor_id'],
            'array doctor' => ['doctor_id', ['invalid'], 'doctor_id'],
            'missing date' => ['appointment_date', null, 'appointment_date'],
            'past date' => ['appointment_date', '2026-09-13', 'appointment_date'],
            'invalid date' => ['appointment_date', '2026-02-30', 'appointment_date'],
            'array date' => ['appointment_date', ['invalid'], 'appointment_date'],
            'non working day' => ['appointment_date', '2026-09-16', 'appointment_time'],
            'missing time' => ['appointment_time', null, 'appointment_time'],
            'invalid time' => ['appointment_time', '25:00', 'appointment_time'],
            'time with seconds' => ['appointment_time', '09:00:30', 'appointment_time'],
            'array time' => ['appointment_time', ['invalid'], 'appointment_time'],
            'before shift' => ['appointment_time', '08:30', 'appointment_time'],
            'end of shift' => ['appointment_time', '12:00', 'appointment_time'],
            'incomplete duration' => ['appointment_time', '11:45', 'appointment_time'],
            'off grid' => ['appointment_time', '09:15', 'appointment_time'],
            'missing reason' => ['reason', '', 'reason'],
            'array reason' => ['reason', ['invalid'], 'reason'],
            'long reason' => ['reason', str_repeat('a', 256), 'reason'],
        ];
    }

    #[TestWith(['doctor_id', ['invalid']])]
    #[TestWith(['doctor_id', 999999])]
    #[TestWith(['appointment_date', '2026-09-13'])]
    #[TestWith(['appointment_date', ['invalid']])]
    public function test_slot_selection_validates_query_parameters(string $field, mixed $value): void
    {
        $doctor = $this->doctorWithSchedule();
        $this->actingAs(User::factory()->create())->get(route('patient.appointments.create', [
            'doctor_id' => $doctor->id, 'appointment_date' => '2026-09-15', $field => $value,
        ]))->assertViewHas('slots', [])->assertSeeText('Please correct the following fields:');
    }

    public function test_booking_requires_csrf_and_does_not_write_without_it(): void
    {
        $doctor = $this->doctorWithSchedule();
        $this->app->instance('env', 'local');
        $this->actingAs(User::factory()->create())->post(route('patient.appointments.store'), $this->payload($doctor))->assertStatus(419);
        $this->assertDatabaseEmpty('appointments');
    }

    private function doctorWithSchedule(): Doctor
    {
        $this->travelTo(Carbon::parse('2026-09-14 08:00:00'));
        $doctor = Doctor::factory()->create();
        DoctorSchedule::factory()->for($doctor)->create(['day' => 'Tuesday', 'start_time' => '09:00:00', 'end_time' => '12:00:00']);

        return $doctor;
    }

    /** @return array{doctor_id: int, appointment_date: string, appointment_time: string, reason: string} */
    private function payload(Doctor $doctor): array
    {
        return ['doctor_id' => $doctor->id, 'appointment_date' => '2026-09-15', 'appointment_time' => '09:00', 'reason' => 'Consultation request'];
    }
}
