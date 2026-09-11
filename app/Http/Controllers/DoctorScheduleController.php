<?php

namespace App\Http\Controllers;

use App\AppointmentAvailability;
use App\Http\Requests\SaveDoctorScheduleRequest;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DoctorScheduleController extends Controller
{
    public function index(Request $request): View
    {
        return view('doctor.schedule', [
            'schedules' => $request->user()->doctor()->firstOrFail()->schedules()->orderBy('day')->orderBy('start_time')->get(),
        ]);
    }

    public function edit(Request $request, string $schedule): View
    {
        return view('doctor.edit-schedule', [
            'schedule' => $request->user()->doctor()->firstOrFail()->schedules()->findOrFail($schedule),
        ]);
    }

    public function store(SaveDoctorScheduleRequest $request): RedirectResponse
    {
        return $this->save($request);
    }

    public function update(SaveDoctorScheduleRequest $request, string $schedule): RedirectResponse
    {
        return $this->save($request, $schedule);
    }

    public function destroy(Request $request, string $schedule): RedirectResponse
    {
        DB::transaction(function () use ($request, $schedule): void {
            $doctor = $request->user()->doctor()->lockForUpdate()->firstOrFail();
            $entry = $doctor->schedules()->lockForUpdate()->findOrFail($schedule);
            $day = $entry->day;
            $entry->delete();
            $this->ensureUpcomingAppointmentsAreCovered($doctor, $day);
        }, 3);

        return redirect()->route('doctor.schedule.index')->with('status', 'Schedule entry deleted.');
    }

    private function save(SaveDoctorScheduleRequest $request, ?string $scheduleId = null): RedirectResponse
    {
        DB::transaction(function () use ($request, $scheduleId): void {
            /** Use the same doctor lock as booking to protect working hours during concurrent requests. */
            $doctor = $request->user()->doctor()->lockForUpdate()->firstOrFail();
            $entry = $scheduleId !== null ? $doctor->schedules()->lockForUpdate()->findOrFail($scheduleId) : null;
            $validated = $request->validated();
            $overlaps = $doctor->schedules()->where('day', $validated['day'])
                ->where('start_time', '<', $validated['end_time'])->where('end_time', '>', $validated['start_time']);

            if ($entry) {
                $overlaps->where('id', '!=', $entry->id);
            }

            if ($overlaps->exists()) {
                throw ValidationException::withMessages(['start_time' => 'These working hours overlap an existing schedule entry.']);
            }

            if ($entry) {
                $previousDay = $entry->day;
                $entry->update($validated);
                $this->ensureUpcomingAppointmentsAreCovered($doctor, $previousDay);
            } else {
                $doctor->schedules()->create($validated);
            }
        }, 3);

        return redirect()->route('doctor.schedule.index')->with('status', 'Working schedule saved.');
    }

    private function ensureUpcomingAppointmentsAreCovered(Doctor $doctor, string $day): void
    {
        $now = CarbonImmutable::now();
        $schedules = $doctor->schedules()->where('day', $day)->get();
        $appointments = $doctor->appointments()->whereIn('status', ['pending', 'confirmed'])
            ->where('appointment_date', '>=', $now->toDateString())->get();

        foreach ($appointments as $appointment) {
            $date = $appointment->appointment_date->toDateString();
            $start = CarbonImmutable::parse($date.' '.$appointment->appointment_time);

            if ($start->lt($now) || $start->format('l') !== $day) {
                continue;
            }

            $end = $start->addMinutes(AppointmentAvailability::DurationMinutes);
            $covered = $schedules->contains(fn (DoctorSchedule $schedule): bool => $start->gte(CarbonImmutable::parse($date.' '.$schedule->start_time))
                && $end->lte(CarbonImmutable::parse($date.' '.$schedule->end_time))
            );

            if (! $covered) {
                throw ValidationException::withMessages([
                    'schedule' => 'This change would leave an upcoming pending or confirmed appointment outside your working hours.',
                ]);
            }
        }
    }
}
