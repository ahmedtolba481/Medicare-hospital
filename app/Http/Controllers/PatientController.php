<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PatientController extends Controller
{
    public function dashboard(Request $request): View
    {
        $patient = $request->user();
        $now = now();
        $upcoming = $patient->appointments()->whereIn('status', ['pending', 'confirmed'])
            ->where(function (Builder $query) use ($now): void {
                $query->where('appointment_date', '>', $now->toDateString())
                    ->orWhere(function (Builder $query) use ($now): void {
                        $query->where('appointment_date', $now->toDateString())
                            ->where('appointment_time', '>=', $now->format('H:i:s'));
                    });
            });
        $statusCounts = $patient->appointments()->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        return view('patient.dashboard', [
            'patient' => $patient,
            'statusCounts' => $statusCounts,
            'totalAppointments' => $statusCounts->sum(),
            'upcomingCount' => (clone $upcoming)->count(),
            'upcomingAppointments' => $upcoming->with(['doctor.user', 'doctor.department'])
                ->orderBy('appointment_date')->orderBy('appointment_time')->orderBy('id')->limit(5)->get(),
            'recentAppointments' => $patient->appointments()->with(['doctor.user', 'doctor.department'])
                ->latest('created_at')->latest('id')->limit(5)->get(),
        ]);
    }

    public function appointments(Request $request): View
    {
        return view('patient.appointments', [
            'appointments' => $request->user()->appointments()->with(['doctor.user', 'doctor.department'])
                ->orderByDesc('appointment_date')->orderByDesc('appointment_time')->orderByDesc('id')->paginate(15),
        ]);
    }

    public function showAppointment(Request $request, string $appointment): View
    {
        return view('patient.appointment', [
            'appointment' => $request->user()->appointments()->with(['doctor.user', 'doctor.department'])
                ->findOrFail($appointment),
        ]);
    }

    public function cancelAppointment(Request $request, string $appointment): RedirectResponse
    {
        $cancelledAppointment = DB::transaction(function () use ($request, $appointment): Appointment {
            $patientAppointment = $request->user()->appointments()->lockForUpdate()->findOrFail($appointment);
            Gate::authorize('cancel', $patientAppointment);

            if (! $patientAppointment->isCancellable()) {
                throw ValidationException::withMessages([
                    'appointment' => 'Only future pending or confirmed appointments can be cancelled.',
                ])->redirectTo(route('patient.appointments.show', $patientAppointment));
            }

            $patientAppointment->status = 'cancelled';
            $patientAppointment->save();

            return $patientAppointment;
        }, 3);

        return redirect()->route('patient.appointments.show', $cancelledAppointment)
            ->with('status', 'Your appointment has been cancelled.');
    }

    public function profile(Request $request): View
    {
        return view('patient.profile', ['patient' => $request->user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $patient = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($patient)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'date_of_birth' => ['nullable', 'date_format:Y-m-d', 'before:today'],
        ]);

        $patient->fill($validated);

        if ($patient->isDirty('email')) {
            $patient->email_verified_at = null;
        }

        $patient->save();

        return redirect()->route('patient.profile')->with('status', 'Your profile has been updated.');
    }

    public function messages(Request $request): View
    {
        return view('patient.messages', [
            'messages' => $request->user()->contactMessages()->latest('created_at')->latest('id')->paginate(10),
        ]);
    }

    public function storeMessage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);
        $patient = $request->user();

        $patient->contactMessages()->create([
            ...$validated,
            'name' => $patient->name,
            'email' => $patient->email,
            'phone' => $patient->phone,
        ]);

        return redirect()->route('patient.messages.index')->with('status', 'Your message has been sent to our care team.');
    }
}
