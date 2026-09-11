<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Department;
use App\Models\Doctor;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PublicSiteController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'departments' => Department::query()->withCount('doctors')->orderBy('name')->limit(3)->get(),
            'doctors' => Doctor::query()->with(['department', 'user'])->orderBy('id')->limit(3)->get(),
            'departmentCount' => Department::query()->count(),
            'doctorCount' => Doctor::query()->count(),
        ]);
    }

    public function about(): View
    {
        return view('public.about', [
            'departmentCount' => Department::query()->count(),
            'doctorCount' => Doctor::query()->count(),
        ]);
    }

    public function services(): View
    {
        return view('public.services');
    }

    public function departments(): View
    {
        return view('public.departments', [
            'departments' => Department::query()->withCount('doctors')->orderBy('name')->get(),
        ]);
    }

    public function showDepartment(Department $department): View
    {
        $department->load(['doctors' => fn (HasMany $query): HasMany => $query->with(['user', 'department'])->orderBy('id')]);

        return view('public.departments.show', compact('department'));
    }

    public function doctors(Request $request): View|RedirectResponse
    {
        $validator = Validator::make($request->query(), [
            'search' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'integer', 'exists:departments,id'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('doctors.index')->withErrors($validator);
        }

        $filters = $validator->validated();
        $search = $filters['search'] ?? '';
        $departmentId = $filters['department'] ?? null;
        $doctors = Doctor::query()->with(['department', 'user'])->orderBy('id');

        if ($departmentId !== null) {
            $doctors->where('department_id', $departmentId);
        }

        if ($search !== '') {
            $pattern = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search).'%';

            $doctors->where(function (Builder $query) use ($pattern): void {
                $query->where('specialization', 'like', $pattern)
                    ->orWhereHas('user', fn (Builder $query): Builder => $query->where('name', 'like', $pattern))
                    ->orWhereHas('department', fn (Builder $query): Builder => $query->where('name', 'like', $pattern));
            });
        }

        return view('public.doctors', [
            'doctors' => $doctors->get(),
            'departments' => Department::query()->orderBy('name')->get(),
            'search' => $search,
            'departmentId' => $departmentId,
        ]);
    }

    public function showDoctor(Doctor $doctor): View
    {
        $doctor->load([
            'department',
            'user',
            'schedules' => fn (HasMany $query): HasMany => $query->orderBy('day')->orderBy('start_time'),
        ]);

        return view('public.doctors.show', compact('doctor'));
    }

    public function contact(): View
    {
        return view('public.contact');
    }

    public function storeContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactMessage::create($validated);

        return redirect()->route('contact')->with('status', 'Thank you. Our care team will be in touch soon.');
    }
}
