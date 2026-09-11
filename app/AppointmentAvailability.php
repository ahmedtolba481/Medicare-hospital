<?php

namespace App;

use App\Models\Doctor;
use Carbon\CarbonImmutable;

class AppointmentAvailability
{
    public const int DurationMinutes = 30;

    /** @return list<string> */
    public function slots(Doctor $doctor, string $date): array
    {
        $day = CarbonImmutable::parse($date)->startOfDay();
        $now = CarbonImmutable::now();

        if ($day->lt($now->startOfDay())) {
            return [];
        }

        $schedules = $doctor->schedules()->where('day', $day->format('l'))->orderBy('start_time')->get();
        $bookedIntervals = $doctor->appointments()->where('appointment_date', $date)
            ->pluck('appointment_time')->map(function (string $time) use ($date): array {
                $start = CarbonImmutable::parse($date.' '.$time);

                return [$start, $start->addMinutes(self::DurationMinutes)];
            });
        $slots = [];

        foreach ($schedules as $schedule) {
            $start = CarbonImmutable::parse($date.' '.$schedule->start_time);
            $end = CarbonImmutable::parse($date.' '.$schedule->end_time);

            if ($start->second !== 0) {
                $start = $start->addMinute()->startOfMinute();
            }

            for ($slot = $start; $slot->addMinutes(self::DurationMinutes)->lte($end); $slot = $slot->addMinutes(self::DurationMinutes)) {
                $slotEnd = $slot->addMinutes(self::DurationMinutes);
                $overlaps = $bookedIntervals->contains(
                    fn (array $interval): bool => $slot->lt($interval[1]) && $slotEnd->gt($interval[0])
                );

                if ($slot->gte($now) && ! $overlaps) {
                    $slots[$slot->format('H:i')] = $slot->format('H:i');
                }
            }
        }

        ksort($slots);

        return array_values($slots);
    }
}
