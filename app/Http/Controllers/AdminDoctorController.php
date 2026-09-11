<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveAdminDoctorRequest;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        $imagePath = $this->storeImage($request->file('image'));

        try {
            DB::transaction(function () use ($request, $imagePath): void {
                $this->lockDepartment($request->integer('department_id'));
                $user = User::create([...$request->safe()->only(['name', 'email', 'phone', 'password']), 'role' => 'doctor']);
                $user->doctor()->create([
                    ...$request->safe()->only(['department_id', 'specialization', 'experience', 'education', 'bio']),
                    'image' => $imagePath,
                ]);
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            $this->deleteStoredImage($imagePath);
            throw ValidationException::withMessages(['email' => 'This email address is already in use.']);
        } catch (\Throwable $exception) {
            $this->deleteStoredImage($imagePath);
            throw $exception;
        }

        return redirect()->route('admin.doctors.index')->with('status', 'Doctor created successfully.');
    }

    public function edit(Doctor $doctor): View
    {
        return view('admin.doctor-form', ['doctor' => $doctor->load('user'), 'departments' => Department::orderBy('name')->orderBy('id')->get()]);
    }

    public function update(SaveAdminDoctorRequest $request, Doctor $doctor): RedirectResponse
    {
        $imagePath = $this->storeImage($request->file('image'));
        $oldImagePath = $doctor->image;

        try {
            DB::transaction(function () use ($request, $doctor, $imagePath): void {
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
                $profile->update([
                    ...$request->safe()->only(['department_id', 'specialization', 'experience', 'education', 'bio']),
                    ...($imagePath === null ? [] : ['image' => $imagePath]),
                ]);
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            $this->deleteStoredImage($imagePath);
            throw ValidationException::withMessages(['email' => 'This email address is already in use.']);
        } catch (\Throwable $exception) {
            $this->deleteStoredImage($imagePath);
            throw $exception;
        }

        if ($imagePath !== null) {
            $this->deleteStoredImage($oldImagePath);
        }

        return redirect()->route('admin.doctors.index')->with('status', 'Doctor updated successfully.');
    }

    public function destroy(Doctor $doctor): RedirectResponse
    {
        $imagePath = $doctor->image;

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

        $this->deleteStoredImage($imagePath);

        return redirect()->route('admin.doctors.index')->with('status', 'Doctor and login account deleted successfully.');
    }

    private function storeImage(?UploadedFile $image): ?string
    {
        if ($image === null) {
            return null;
        }

        $path = Storage::disk('public')->putFile('doctors', $image);

        if ($path === false) {
            throw new \RuntimeException('The doctor image could not be stored.');
        }

        return $path;
    }

    private function deleteStoredImage(?string $imagePath): void
    {
        if ($imagePath !== null && ! Str::startsWith($imagePath, ['https://', 'http://'])) {
            Storage::disk('public')->delete($imagePath);
        }
    }

    private function lockDepartment(int $departmentId): void
    {
        if (! Department::whereKey($departmentId)->lockForUpdate()->first()) {
            throw ValidationException::withMessages(['department_id' => 'The selected department is no longer available.']);
        }
    }
}
