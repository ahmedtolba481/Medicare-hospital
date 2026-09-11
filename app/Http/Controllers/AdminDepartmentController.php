<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveAdminDepartmentRequest;
use App\Models\Department;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminDepartmentController extends Controller
{
    public function index(): View
    {
        return view('admin.departments', ['departments' => Department::withCount('doctors')->orderBy('name')->orderBy('id')->paginate(15)]);
    }

    public function create(): View
    {
        return view('admin.department-form', ['department' => null]);
    }

    public function store(SaveAdminDepartmentRequest $request): RedirectResponse
    {
        try {
            Department::create($request->validated());
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['name' => 'A department with this name already exists.']);
        }

        return redirect()->route('admin.departments.index')->with('status', 'Department created successfully.');
    }

    public function edit(Department $department): View
    {
        return view('admin.department-form', compact('department'));
    }

    public function update(SaveAdminDepartmentRequest $request, Department $department): RedirectResponse
    {
        try {
            $department->update($request->validated());
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['name' => 'A department with this name already exists.']);
        }

        return redirect()->route('admin.departments.index')->with('status', 'Department updated successfully.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        DB::transaction(function () use ($department): void {
            $record = Department::whereKey($department->id)->lockForUpdate()->firstOrFail();
            if ($record->doctors()->lockForUpdate()->first(['id'])) {
                throw ValidationException::withMessages(['department' => 'Reassign all doctors before deleting this department.']);
            }
            $record->delete();
        }, 3);

        return redirect()->route('admin.departments.index')->with('status', 'Department deleted successfully.');
    }
}
