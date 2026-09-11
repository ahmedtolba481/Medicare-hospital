<?php

namespace Tests\Feature;

use App\AppointmentAvailability;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class DoctorScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_can_create_edit_and_delete_own_hours_and_booking_slots_follow_changes(): void
    {
        $this->travelTo(Carbon::parse('2026-09-14 08:00'));
        $doctor = Doctor::factory()->create();
        $this->actingAs($doctor->user)->post(route('doctor.schedule.store'), ['day' => 'Tuesday', 'start_time' => '09:00', 'end_time' => '10:00', 'doctor_id' => 99999])->assertRedirect(route('doctor.schedule.index'));
        $entry = $doctor->schedules()->sole();
        $this->get(route('doctor.schedule.index'))->assertOk()->assertSeeText('Tuesday');
        $this->get(route('doctor.schedule.edit', $entry))->assertOk();
        $this->assertSame(['09:00', '09:30'], app(AppointmentAvailability::class)->slots($doctor, '2026-09-15'));
        $this->patch(route('doctor.schedule.update', $entry), ['day' => 'Tuesday', 'start_time' => '10:00', 'end_time' => '11:00'])->assertRedirect();
        $this->assertSame(['10:00', '10:30'], app(AppointmentAvailability::class)->slots($doctor, '2026-09-15'));
        $this->delete(route('doctor.schedule.destroy', $entry))->assertRedirect()->assertSessionHas('status', 'Schedule entry deleted.');
        $this->assertDatabaseMissing('doctor_schedules', ['id' => $entry->id]);
        $this->assertSame([], app(AppointmentAvailability::class)->slots($doctor, '2026-09-15'));
    }

    public function test_foreign_schedule_cannot_be_read_updated_or_deleted(): void
    {
        $doctor = Doctor::factory()->create();
        $entry = DoctorSchedule::factory()->create(['day' => 'Tuesday', 'start_time' => '09:00', 'end_time' => '12:00']);
        $original = $entry->fresh()->getAttributes();
        $this->actingAs($doctor->user)->get(route('doctor.schedule.index'))->assertOk()->assertViewHas('schedules', fn ($schedules): bool => $schedules->isEmpty());
        $this->get(route('doctor.schedule.edit', $entry))->assertNotFound();
        $this->patchJson(route('doctor.schedule.update', $entry), ['day' => 'Monday', 'start_time' => '09:00', 'end_time' => '10:00'])->assertNotFound();
        $this->deleteJson(route('doctor.schedule.destroy', $entry))->assertNotFound();
        $this->assertSame($original, $entry->fresh()->getAttributes());
    }

    #[TestWith(['Funday', '09:00', '10:00', 'day'])]
    #[TestWith(['Monday', 'bad', '10:00', 'start_time'])]
    #[TestWith(['Monday', '09:00', '09:00', 'end_time'])]
    #[TestWith(['Monday', '09:00', '08:00', 'end_time'])]
    #[TestWith(['Monday', '09:00', '09:29', 'end_time'])]
    #[TestWith(['Monday', '23:30', '00:30', 'end_time'])]
    public function test_invalid_hours_are_rejected(string $day, string $start, string $end, string $field): void
    {
        $doctor = Doctor::factory()->create();
        $this->actingAs($doctor->user)->postJson(route('doctor.schedule.store'), ['day' => $day, 'start_time' => $start, 'end_time' => $end])->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertDatabaseCount('doctor_schedules', 0);
    }

    public function test_overlapping_hours_are_rejected_but_adjacent_hours_and_unchanged_edits_are_allowed(): void
    {
        $entry = DoctorSchedule::factory()->create(['day' => 'Tuesday', 'start_time' => '09:00', 'end_time' => '12:00']);
        $this->actingAs($entry->doctor->user);
        foreach ([['09:00', '12:00'], ['08:30', '09:30'], ['11:30', '12:30'], ['10:00', '11:00'], ['08:00', '13:00']] as [$start, $end]) {
            $this->postJson(route('doctor.schedule.store'), ['day' => 'Tuesday', 'start_time' => $start, 'end_time' => $end])->assertUnprocessable()->assertJsonValidationErrors('start_time');
        }
        $this->patch(route('doctor.schedule.update', $entry), ['day' => 'Tuesday', 'start_time' => '09:00', 'end_time' => '12:00'])->assertRedirect();
        $this->post(route('doctor.schedule.store'), ['day' => 'Tuesday', 'start_time' => '12:00', 'end_time' => '12:30'])->assertRedirect();
        $this->assertDatabaseCount('doctor_schedules', 2);
    }

    #[TestWith(['pending'])]
    #[TestWith(['confirmed'])]
    public function test_upcoming_bookings_prevent_removal_day_changes_and_partial_slot_coverage(string $status): void
    {
        $this->travelTo(Carbon::parse('2026-09-14 08:00'));
        $entry = DoctorSchedule::factory()->create(['day' => 'Tuesday', 'start_time' => '09:00', 'end_time' => '12:00']);
        Appointment::factory()->create(['doctor_id' => $entry->doctor_id, 'appointment_date' => '2026-09-15', 'appointment_time' => '10:00', 'status' => $status]);
        $original = $entry->fresh()->getAttributes();
        $this->actingAs($entry->doctor->user)->deleteJson(route('doctor.schedule.destroy', $entry))->assertUnprocessable()->assertJsonValidationErrors('schedule');
        foreach ([['Wednesday', '09:00', '12:00'], ['Tuesday', '09:00', '10:15'], ['Tuesday', '10:15', '12:00']] as [$day, $start, $end]) {
            $this->patchJson(route('doctor.schedule.update', $entry), ['day' => $day, 'start_time' => $start, 'end_time' => $end])->assertUnprocessable()->assertJsonValidationErrors('schedule');
            $this->assertSame($original, $entry->fresh()->getAttributes());
        }
        $this->patch(route('doctor.schedule.update', $entry), ['day' => 'Tuesday', 'start_time' => '10:00', 'end_time' => '10:30'])->assertRedirect();
    }

    #[TestWith(['completed', '2026-09-15'])]
    #[TestWith(['cancelled', '2026-09-15'])]
    #[TestWith(['rejected', '2026-09-15'])]
    #[TestWith(['pending', '2026-09-08'])]
    #[TestWith(['confirmed', '2026-09-08'])]
    public function test_terminal_and_past_visits_do_not_block_schedule_removal(string $status, string $date): void
    {
        $this->travelTo(Carbon::parse('2026-09-14 08:00'));
        $entry = DoctorSchedule::factory()->create(['day' => 'Tuesday', 'start_time' => '09:00', 'end_time' => '12:00']);
        Appointment::factory()->create(['doctor_id' => $entry->doctor_id, 'appointment_date' => $date, 'appointment_time' => '10:00', 'status' => $status]);
        $this->actingAs($entry->doctor->user)->delete(route('doctor.schedule.destroy', $entry))->assertRedirect();
        $this->assertDatabaseMissing('doctor_schedules', ['id' => $entry->id]);
    }
}
