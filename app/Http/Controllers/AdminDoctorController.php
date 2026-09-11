<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveAdminDoctorRequest;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminDoctorController extends Controller
{
    public function index(): View
    {
        return view('admin.doctors', [
            'doctors' => Doctor::with(['user', 'department'])->withCount('appointments')->orderBy('id')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.doctor-form', ['doctor' => null, 'departments' => Department::orderBy('name')->orderBy('id')->get()]);
    }

    public function store(SaveAdminDoctorRequest $request): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request): void {
                $this->lockDepartment($request->integer('department_id'));
                $user = User::create([...$request->safe()->only(['name', 'email', 'phone', 'password']), 'role' => 'doctor']);
                $user->doctor()->create($request->safe()->only(['department_id', 'specialization', 'experience', 'education', 'bio']));
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['email' => 'This email address is already in use.']);
        }

        return redirect()->route('admin.doctors.index')->with('status', 'Doctor created successfully.');
    }

    public function edit(Doctor $doctor): View
    {
        return view('admin.doctor-form', ['doctor' => $doctor->load('user'), 'departments' => Department::orderBy('name')->orderBy('id')->get()]);
    }

    public function update(SaveAdminDoctorRequest $request, Doctor $doctor): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $doctor): void {
                $this->lockDepartment($request->integer('department_id'));
                $profile = Doctor::whereKey($doctor->id)->lockForUpdate()->firstOrFail();
                $user = $profile->user()->lockForUpdate()->firstOrFail();
                if ($user->role !== 'doctor') {
                    throw ValidationException::withMessages(['doctor' => 'This profile must be linked to a doctor account before it can be edited.']);
                }
                $user->fill($request->safe()->only(['name', 'email', 'phone']));
                if ($user->isDirty('email')) {
                    $user->email_verified_at = null;
                }
                $user->save();
                $profile->update($request->safe()->only(['department_id', 'specialization', 'experience', 'education', 'bio']));
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['email' => 'This email address is already in use.']);
        }

        return redirect()->route('admin.doctors.index')->with('status', 'Doctor updated successfully.');
    }

    public function destroy(Doctor $doctor): RedirectResponse
    {
        DB::transaction(function () use ($doctor): void {
            $profile = Doctor::whereKey($doctor->id)->lockForUpdate()->firstOrFail();
            $user = $profile->user()->lockForUpdate()->firstOrFail();
            if ($profile->appointments()->lockForUpdate()->first(['id']) || $user->appointments()->lockForUpdate()->first(['id'])) {
                throw ValidationException::withMessages(['doctor' => 'This doctor has appointment history and cannot be deleted.']);
            }
            if ($user->role !== 'doctor') {
                throw ValidationException::withMessages(['doctor' => 'Only a doctor account can be deleted here.']);
            }

            $user->delete();
        }, 3);

        return redirect()->route('admin.doctors.index')->with('status', 'Doctor and login account deleted successfully.');
    }

    private function lockDepartment(int $departmentId): void
    {
        if (! Department::whereKey($departmentId)->lockForUpdate()->first()) {
            throw ValidationException::withMessages(['department_id' => 'The selected department is no longer available.']);
        }
    }
}
