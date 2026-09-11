@extends('layouts.admin', ['title' => 'Doctors'])
@section('admin-content')
    <section class="card app-panel p-4">
        <div class="d-flex flex-wrap justify-content-between gap-3 mb-3"><h2 class="h4 mb-0">Doctor directory</h2><a class="btn btn-primary" href="{{ route('admin.doctors.create') }}">Create doctor</a></div>
        <p class="small text-secondary">Doctors with appointment history cannot be deleted.</p>
        @if ($doctors->isEmpty())
            <x-dashboard.empty-state title="No doctors found" message="No doctors have been added yet." icon="stethoscope" />
        @else
            <div class="table-responsive">
                <table class="table app-table align-middle">
                    <thead><tr><th scope="col">Doctor</th><th scope="col">Department</th><th scope="col">Specialization</th><th scope="col">Appointments</th><th scope="col">Actions</th></tr></thead>
                    <tbody>
                        @foreach ($doctors as $doctor)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <x-public.doctor-photo :doctor="$doctor" class="doctor-avatar-sm" />
                                        <span class="text-break">{{ $doctor->user->name }}<br><span class="small text-secondary">{{ $doctor->user->email }}</span></span>
                                    </div>
                                </td>
                                <td>{{ $doctor->department->name }}</td><td>{{ $doctor->specialization }}</td><td>{{ $doctor->appointments_count }}</td>
                                <td><a href="{{ route('admin.doctors.edit', $doctor) }}">Edit</a>
                                    @if ($doctor->appointments_count === 0)
                                        <x-admin.delete-form :action="route('admin.doctors.destroy', $doctor)" :description="'Permanently delete '.$doctor->user->name.' and their login account and working schedule?'" />
                                    @else
                                        <span class="small text-secondary d-block">History retained</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        {{ $doctors->links('pagination::bootstrap-5') }}
    </section>
@endsection
