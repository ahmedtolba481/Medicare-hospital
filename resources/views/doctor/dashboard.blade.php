@extends('layouts.doctor', ['title' => 'Doctor dashboard'])

@section('doctor-content')
    <x-dashboard.welcome :name="$doctor->user->name" copy="Here's your schedule and appointment overview." />

    <div class="row g-3 mb-4">
        <x-dashboard.stat-card :value="$todayAppointments->count()" label="Today's Appointments" icon="calendar" :description="now()->format('M j, Y')" />
        <x-dashboard.stat-card :value="$statusCounts->get('pending', 0)" label="Pending" icon="alert" description="Requests waiting for you" />
        <x-dashboard.stat-card :value="$statusCounts->get('confirmed', 0)" label="Confirmed" icon="check" description="Accepted visits" />
        <x-dashboard.stat-card :value="$statusCounts->get('completed', 0)" label="Completed" icon="clipboard" description="Finished visits" />
        <x-dashboard.stat-card :value="$patientCount" label="Total Patients" icon="users" description="Patients assigned to you" />
    </div>

    <section class="card app-panel p-4 mb-4">
        <div class="app-panel-head">
            <div>
                <h2 class="h5 mb-0">Today's Schedule</h2>
                <p class="small text-secondary mb-0">{{ now()->format('M j, Y') }} · {{ config('app.timezone') }}</p>
            </div>
            <a class="app-quiet-link" href="{{ route('doctor.appointments.index') }}">View all</a>
        </div>
        <x-doctor.appointment-table :appointments="$todayAppointments" show-reason />
    </section>

    <section class="card app-panel p-4 mb-4">
        <div class="app-panel-head">
            <h2 class="h5 mb-0">Pending Appointments</h2>
        </div>
        <x-doctor.appointment-table :appointments="$pendingAppointments" show-reason pending-actions />
    </section>

    <section class="card app-panel p-4 mb-4">
        <div class="app-panel-head">
            <h2 class="h5 mb-0">Upcoming Appointments</h2>
        </div>
        <x-doctor.appointment-table :appointments="$upcomingAppointments" show-reason />
    </section>

    <div class="row g-4">
        <div class="col-lg-7">
            <section class="card app-panel p-4 h-100">
                <h2 class="h5 mb-3">Quick Actions</h2>
                <div class="app-quick-grid">
                    <x-dashboard.quick-action :href="route('doctor.appointments.index')" label="View Appointments" icon="calendar" />
                    <x-dashboard.quick-action :href="route('doctor.schedule.index')" label="Manage Schedule" icon="clock" />
                    <x-dashboard.quick-action :href="route('doctor.patients.index')" label="View Patients" icon="users" />
                    <x-dashboard.quick-action :href="route('doctor.profile')" label="Edit Profile" icon="user" />
                </div>
            </section>
        </div>
        <div class="col-lg-5">
            <section class="card app-panel p-4 h-100">
                <div class="app-panel-head">
                    <h2 class="h5 mb-0">Schedule summary</h2>
                    <a class="app-quiet-link" href="{{ route('doctor.schedule.index') }}">Edit</a>
                </div>
                @forelse ($schedules as $schedule)
                    <div class="d-flex justify-content-between gap-3 border-bottom py-2">
                        <strong>{{ $schedule->day }}</strong>
                        <span class="text-secondary">{{ substr($schedule->start_time, 0, 5) }}–{{ substr($schedule->end_time, 0, 5) }}</span>
                    </div>
                @empty
                    <x-dashboard.empty-state title="No working hours" message="No working hours have been added yet." icon="clock">
                        <a class="btn btn-primary btn-sm" href="{{ route('doctor.schedule.index') }}">Add hours</a>
                    </x-dashboard.empty-state>
                @endforelse
            </section>
        </div>
    </div>
@endsection
