@extends('layouts.admin', ['title' => 'Departments'])
@section('admin-content')
    <section class="card app-panel p-4">
        <div class="d-flex flex-wrap justify-content-between gap-3 mb-3"><h2 class="h4 mb-0">Hospital departments</h2><a class="btn btn-primary" href="{{ route('admin.departments.create') }}">Create department</a></div>
        <p class="small text-secondary">Reassign a department's doctors before deleting it.</p>
        @if ($departments->isEmpty())
            <x-dashboard.empty-state title="No departments found" message="No departments have been added yet." icon="building" />
        @else
            <div class="table-responsive">
                <table class="table app-table align-middle">
                    <thead><tr><th scope="col">Department</th><th scope="col">Description</th><th scope="col">Doctors</th><th scope="col">Actions</th></tr></thead>
                    <tbody>
                        @foreach ($departments as $department)
                            <tr><td>{{ $department->name }}</td><td class="text-break">{{ \Illuminate\Support\Str::limit($department->description, 120) }}</td><td>{{ $department->doctors_count }}</td><td>
                                <a href="{{ route('admin.departments.edit', $department) }}">Edit</a>
                                @if ($department->doctors_count === 0)
                                    <x-admin.delete-form :action="route('admin.departments.destroy', $department)" :description="'Permanently delete '.$department->name.'?'" />
                                @endif
                            </td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        {{ $departments->links('pagination::bootstrap-5') }}
    </section>
@endsection
