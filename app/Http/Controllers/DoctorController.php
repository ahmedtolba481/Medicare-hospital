<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDoctorProfileRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DoctorController extends Controller
{
    public function dashboard(Request $request): View
    {
        $doctor = $this->doctor($request);
        $counts = $doctor->appointments()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $now = now();
        $upcoming = $doctor->appointments()->with('patient')->where('status', 'confirmed')
            ->where(function (Builder $query) use ($now): void {
                $query->where('appointment_date', '>', $now->toDateString())
                    ->orWhere(function (Builder $query) use ($now): void {
                        $query->where('appointment_date', $now->toDateString())
                            ->where('appointment_time', '>=', $now->format('H:i:s'));
                    });
            });

        return view('doctor.dashboard', [
            'doctor' => $doctor->load('user'),
            'statusCounts' => $counts,
            'patientCount' => $doctor->appointments()->distinct()->count('patient_id'),
            'todayAppointments' => $doctor->appointments()->with('patient')
                ->where('appointment_date', now()->toDateString())->orderBy('appointment_time')->orderBy('id')->get(),
            'pendingAppointments' => $doctor->appointments()->with('patient')->where('status', 'pending')
                ->orderBy('appointment_date')->orderBy('appointment_time')->orderBy('id')->limit(5)->get(),
            'upcomingAppointments' => $upcoming->orderBy('appointment_date')->orderBy('appointment_time')->orderBy('id')->limit(5)->get(),
            'schedules' => $doctor->schedules()->orderBy('id')->get(),
        ]);
    }

    public function appointments(Request $request): View
    {
        return view('doctor.appointments', [
            'appointments' => $this->doctor($request)->appointments()->with('patient')
                ->orderByDesc('appointment_date')->orderBy('appointment_time')->orderBy('id')->paginate(15),
        ]);
    }

    public function showAppointment(Request $request, string $appointment): View
    {
        return view('doctor.appointment', [
            'appointment' => $this->doctor($request)->appointments()->with('patient')->findOrFail($appointment),
        ]);
    }

    public function updateAppointment(Request $request, string $appointment): RedirectResponse
    {
        $doctor = $this->doctor($request);
        $updatedAppointment = DB::transaction(function () use ($request, $appointment, $doctor): Appointment {
            $visit = $doctor->appointments()->lockForUpdate()->findOrFail($appointment);
            Gate::authorize('manage', $visit);
            $validated = $request->validate([
                'action' => ['required', Rule::in(['accept', 'reject', 'complete', 'notes'])],
                'notes' => ['nullable', 'string', 'max:5000'],
            ]);
            $transitions = ['accept' => ['pending', 'confirmed'], 'reject' => ['pending', 'rejected'], 'complete' => ['confirmed', 'completed']];
            $action = $validated['action'];

            if ($action === 'notes') {
                if (! in_array($visit->status, ['pending', 'confirmed', 'completed'], true)) {
                    throw ValidationException::withMessages(['action' => 'Notes cannot be changed on a cancelled or rejected appointment.']);
                }
            } else {
                [$requiredStatus, $nextStatus] = $transitions[$action];

                if ($visit->status !== $requiredStatus) {
                    throw ValidationException::withMessages(['action' => 'This action is not available for the appointment’s current status. Refresh the appointment and try again.']);
                }

                $visit->status = $nextStatus;
            }

            if (array_key_exists('notes', $validated)) {
                $visit->notes = $validated['notes'];
            }

            $visit->save();

            return $visit;
        }, 3);

        return redirect()->route('doctor.appointments.show', $updatedAppointment)->with('status', 'Appointment updated successfully.');
    }

    public function patients(Request $request): View
    {
        $doctor = $this->doctor($request);

        return view('doctor.patients', [
            'patients' => User::query()->whereHas('appointments', fn (Builder $query): Builder => $query->where('doctor_id', $doctor->id))
                ->withCount(['appointments as doctor_appointments_count' => fn (Builder $query): Builder => $query->where('doctor_id', $doctor->id)])
                ->orderBy('name')->orderBy('id')->paginate(15),
        ]);
    }

    public function showPatient(Request $request, string $patient): View
    {
        $doctor = $this->doctor($request);
        $patientRecord = User::query()->whereHas('appointments', fn (Builder $query): Builder => $query->where('doctor_id', $doctor->id))->findOrFail($patient);

        return view('doctor.patient', [
            'patient' => $patientRecord,
            'appointments' => $doctor->appointments()->with('patient')->where('patient_id', $patientRecord->id)
                ->orderByDesc('appointment_date')->orderByDesc('id')->paginate(15),
        ]);
    }

    public function profile(Request $request): View
    {
        return view('doctor.profile', ['doctor' => $this->doctor($request)->load(['user', 'department'])]);
    }

    public function updateProfile(UpdateDoctorProfileRequest $request): RedirectResponse
    {
        $doctor = $this->doctor($request);

        DB::transaction(function () use ($request, $doctor): void {
            $user = $request->user();
            $user->fill($request->safe()->only(['name', 'email', 'phone']));

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();
            $doctor->update($request->safe()->only(['specialization', 'experience', 'education', 'bio']));
        });

        return redirect()->route('doctor.profile')->with('status', 'Your doctor profile has been updated.');
    }

    private function doctor(Request $request): Doctor
    {
        return $request->user()->doctor()->firstOrFail();
    }
}
