<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAdminMessageRequest;
use App\Models\Appointment;
use App\Models\ContactMessage;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'statistics' => [
                'Total patients' => User::where('role', 'patient')->count(),
                'Total doctors' => Doctor::count(),
                'Total departments' => Department::count(),
                'Total appointments' => Appointment::count(),
                'Pending appointments' => Appointment::where('status', 'pending')->count(),
                'Confirmed appointments' => Appointment::where('status', 'confirmed')->count(),
                'Unread messages' => ContactMessage::where('status', 'unread')->count(),
            ],
            'appointments' => Appointment::with(['patient', 'doctor.user'])->orderByDesc('created_at')->orderByDesc('id')->limit(5)->get(),
            'recentDoctors' => Doctor::with(['user', 'department'])->orderByDesc('id')->limit(5)->get(),
            'recentPatients' => User::where('role', 'patient')->orderByDesc('id')->limit(5)->get(),
            'recentMessages' => ContactMessage::orderByDesc('created_at')->orderByDesc('id')->limit(5)->get(),
        ]);
    }

    public function patients(): View
    {
        return view('admin.patients', ['patients' => User::where('role', 'patient')->withCount('appointments')->orderBy('name')->orderBy('id')->paginate(15)]);
    }

    public function showPatient(string $patient): View
    {
        $record = User::where('role', 'patient')->findOrFail($patient);

        return view('admin.patient', [
            'patient' => $record,
            'appointments' => $record->appointments()->with(['patient', 'doctor.user'])->orderByDesc('appointment_date')->orderByDesc('id')->paginate(15),
        ]);
    }

    public function appointments(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'string', Rule::in(['pending', 'confirmed', 'completed', 'cancelled', 'rejected'])],
            'doctor_id' => ['nullable', 'integer', Rule::exists('doctors', 'id')],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $query = Appointment::with(['patient', 'doctor.user']);
        foreach (['status' => 'status', 'doctor_id' => 'doctor_id', 'date' => 'appointment_date'] as $filter => $column) {
            if (isset($filters[$filter])) {
                $query->where($column, $filters[$filter]);
            }
        }

        return view('admin.appointments', [
            'appointments' => $query->orderByDesc('appointment_date')->orderBy('appointment_time')->orderBy('id')->paginate(15)->withQueryString(),
            'doctors' => Doctor::with('user')->orderBy('id')->get(),
            'filters' => $filters,
        ]);
    }

    public function showAppointment(Appointment $appointment): View
    {
        return view('admin.appointment', ['appointment' => $appointment->load(['patient', 'doctor.user', 'doctor.department'])]);
    }

    public function messages(): View
    {
        return view('admin.messages', ['messages' => ContactMessage::orderByDesc('created_at')->orderByDesc('id')->paginate(15)]);
    }

    public function showMessage(ContactMessage $message): View
    {
        return view('admin.message', ['contactMessage' => $message]);
    }

    public function updateMessage(UpdateAdminMessageRequest $request, ContactMessage $message): RedirectResponse
    {
        $validated = $request->validated();
        $message->update($validated);

        return redirect()->route('admin.messages.show', $message)->with('status', 'Message marked as '.$validated['status'].'.');
    }

    public function destroyMessage(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('status', 'Contact message deleted successfully.');
    }

    public function profile(): View
    {
        return view('admin.profile', ['admin' => auth()->user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $admin = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($admin)],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $admin->fill($validated);

        if ($admin->isDirty('email')) {
            $admin->email_verified_at = null;
        }

        $admin->save();

        return redirect()->route('admin.profile')->with('status', 'Your profile has been updated.');
    }
}
