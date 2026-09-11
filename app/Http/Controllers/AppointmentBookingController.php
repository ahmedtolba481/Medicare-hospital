<?php

namespace App\Http\Controllers;

use App\AppointmentAvailability;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AppointmentBookingController extends Controller
{
    public function create(Request $request, AppointmentAvailability $availability): View
    {
        $selection = [
            'doctor_id' => $request->query('doctor_id', $request->old('doctor_id')),
            'appointment_date' => $request->query('appointment_date', $request->old('appointment_date')),
        ];
        $validator = Validator::make($selection, [
            'doctor_id' => ['nullable', 'integer', 'exists:doctors,id'],
            'appointment_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);
        $hasErrors = $validator->fails();
        $filters = $hasErrors ? [] : $validator->validated();
        $selectedDoctor = isset($filters['doctor_id']) ? Doctor::query()->with(['user', 'department'])->find($filters['doctor_id']) : null;
        $selectedDate = $filters['appointment_date'] ?? '';
        $slots = $selectedDoctor && $selectedDate !== '' ? $availability->slots($selectedDoctor, $selectedDate) : [];

        $view = view('patient.book-appointment', [
            'doctors' => Doctor::query()->with(['user', 'department'])->orderBy('id')->get(),
            'selectedDoctor' => $selectedDoctor,
            'selectedDate' => $selectedDate,
            'slots' => $slots,
        ]);

        return $hasErrors ? $view->withErrors($validator) : $view;
    }

    public function store(StoreAppointmentRequest $request, AppointmentAvailability $availability): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $appointment = DB::transaction(function () use ($request, $availability, $validated): Appointment {
                /** Serialize bookings for this doctor before checking overlapping slots. */
                $doctor = Doctor::query()->lockForUpdate()->findOrFail($validated['doctor_id']);

                if (! in_array($validated['appointment_time'], $availability->slots($doctor, $validated['appointment_date']), true)) {
                    throw ValidationException::withMessages([
                        'appointment_time' => 'This time is no longer available. Please choose an available time within the doctor’s consultation hours.',
                    ])->redirectTo(route('patient.appointments.create'));
                }

                return $request->user()->appointments()->create([
                    ...$validated,
                    'appointment_time' => $validated['appointment_time'].':00',
                    'status' => 'pending',
                ]);
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages([
                'appointment_time' => 'This time was just booked. Please choose another available time.',
            ])->redirectTo(route('patient.appointments.create'));
        }

        return redirect()->route('patient.appointments.show', $appointment)
            ->with('status', 'Your appointment request has been received. Status: pending.');
    }
}
