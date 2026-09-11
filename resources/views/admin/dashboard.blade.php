@extends('layouts.admin', ['title' => 'Admin dashboard'])

@section('admin-content')
    <x-dashboard.welcome :name="auth()->user()->name" copy="Here's an overview of MediCare Hospital." />

    <div class="row g-3 mb-4">
        @foreach ([
            ['Total Patients', $statistics['Total patients'], 'users', 'Registered patient accounts'],
            ['Total Doctors', $statistics['Total doctors'], 'stethoscope', 'Clinicians on staff'],
            ['Total Departments', $statistics['Total departments'], 'building', 'Clinical specialties'],
            ['Total Appointments', $statistics['Total appointments'], 'calendar', 'All booked visits'],
            ['Pending Appointments', $statistics['Pending appointments'], 'alert', 'Awaiting doctor review'],
            ['Confirmed Appointments', $statistics['Confirmed appointments'], 'check', 'Accepted visits'],
            ['Unread Messages', $statistics['Unread messages'], 'inbox', 'Contact form inbox'],
        ] as [$label, $value, $icon, $description])
            <x-dashboard.stat-card :value="$value" :label="$label" :icon="$icon" :description="$description" />
        @endforeach
    </div>

    <section class="card app-panel p-4 mb-4">
        <div class="app-panel-head">
            <h2 class="h5 mb-0">Appointment overview</h2>
            <a class="app-quiet-link" href="{{ route('admin.appointments.index') }}">View all</a>
        </div>
        <x-admin.appointment-table :appointments="$appointments" />
    </section>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <section class="card app-panel p-4 h-100">
                <div class="app-panel-head">
                    <h2 class="h5 mb-0">Recent Doctors</h2>
                    <a class="app-quiet-link" href="{{ route('admin.doctors.index') }}">Manage</a>
                </div>
                @if ($recentDoctors->isEmpty())
                    <x-dashboard.empty-state title="No doctors found" message="No doctors have been added yet." icon="stethoscope" />
                @else
                    <div class="table-responsive">
                        <table class="table app-table app-table-stack align-middle mb-0">
                            <thead><tr><th scope="col">Doctor</th><th scope="col">Department</th><th scope="col">Action</th></tr></thead>
                            <tbody>
                                @foreach ($recentDoctors as $doctor)
                                    <tr>
                                        <td data-label="Doctor">{{ $doctor->user->name }}<br><span class="small text-secondary">{{ $doctor->specialization }}</span></td>
                                        <td data-label="Department">{{ $doctor->department->name }}</td>
                                        <td data-label="Action"><a class="btn btn-outline-primary btn-sm" href="{{ route('admin.doctors.edit', $doctor) }}">Edit</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
        <div class="col-lg-6">
            <section class="card app-panel p-4 h-100">
                <div class="app-panel-head">
                    <h2 class="h5 mb-0">Recent Patients</h2>
                    <a class="app-quiet-link" href="{{ route('admin.patients.index') }}">Manage</a>
                </div>
                @if ($recentPatients->isEmpty())
                    <x-dashboard.empty-state title="No patients found" message="No patients have registered yet." icon="users" />
                @else
                    <div class="table-responsive">
                        <table class="table app-table app-table-stack align-middle mb-0">
                            <thead><tr><th scope="col">Patient</th><th scope="col">Email</th><th scope="col">Action</th></tr></thead>
                            <tbody>
                                @foreach ($recentPatients as $patient)
                                    <tr>
                                        <td data-label="Patient">{{ $patient->name }}</td>
                                        <td class="text-break" data-label="Email">{{ $patient->email }}</td>
                                        <td data-label="Action"><a class="btn btn-outline-primary btn-sm" href="{{ route('admin.patients.show', $patient) }}">View</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </div>

    <section class="card app-panel p-4 mb-4">
        <div class="app-panel-head">
            <h2 class="h5 mb-0">Messages overview</h2>
            <a class="app-quiet-link" href="{{ route('admin.messages.index') }}">{{ $statistics['Unread messages'] }} unread</a>
        </div>
        @if ($recentMessages->isEmpty())
            <x-dashboard.empty-state title="No messages" message="No contact messages have been received." icon="inbox" />
        @else
            <div class="table-responsive">
                <table class="table app-table app-table-stack align-middle mb-0">
                    <thead><tr><th scope="col">From</th><th scope="col">Subject</th><th scope="col">Status</th><th scope="col">Action</th></tr></thead>
                    <tbody>
                        @foreach ($recentMessages as $message)
                            <tr>
                                <td data-label="From">{{ $message->name }}</td>
                                <td class="text-break" data-label="Subject">{{ $message->subject ?: 'No subject' }}</td>
                                <td data-label="Status"><x-patient.appointment-status :status="$message->status" /></td>
                                <td data-label="Action"><a class="btn btn-outline-primary btn-sm" href="{{ route('admin.messages.show', $message) }}">View</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="card app-panel p-4">
        <h2 class="h5 mb-3">Quick Actions</h2>
        <div class="app-quick-grid">
            <x-dashboard.quick-action :href="route('admin.doctors.create')" label="Add Doctor" icon="plus" />
            <x-dashboard.quick-action :href="route('admin.departments.create')" label="Add Department" icon="building" />
            <x-dashboard.quick-action :href="route('admin.appointments.index')" label="View Appointments" icon="calendar" />
            <x-dashboard.quick-action :href="route('admin.messages.index')" label="View Messages" icon="inbox" />
        </div>
    </section>
@endsection
