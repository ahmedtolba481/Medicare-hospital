@extends('layouts.patient', ['title' => 'Patient dashboard'])

@section('patient-content')
    <x-dashboard.welcome :name="$patient->name" copy="Here's an overview of your healthcare activity." />

    <div class="row g-3 mb-4">
        <x-dashboard.stat-card :value="$totalAppointments" label="Total Appointments" icon="calendar" description="All visits on your record" />
        <x-dashboard.stat-card :value="$upcomingCount" label="Upcoming" icon="clock" description="Pending or confirmed next visits" />
        <x-dashboard.stat-card :value="$statusCounts->get('pending', 0)" label="Pending" icon="alert" description="Awaiting confirmation" />
        <x-dashboard.stat-card :value="$statusCounts->get('confirmed', 0)" label="Confirmed" icon="check" description="Scheduled with your doctor" />
        <x-dashboard.stat-card :value="$statusCounts->get('completed', 0)" label="Completed" icon="clipboard" description="Finished visits" />
        <x-dashboard.stat-card :value="$statusCounts->get('cancelled', 0)" label="Cancelled" icon="x" description="Cancelled by you" />
        <x-dashboard.stat-card :value="$statusCounts->get('rejected', 0)" label="Rejected" icon="x" description="Not accepted by the clinic" />
    </div>

    <section class="card app-panel p-4 mb-4">
        <div class="app-panel-head">
            <h2 class="h5 mb-0">Upcoming Appointments</h2>
            <a class="app-quiet-link" href="{{ route('patient.appointments.index') }}">View all</a>
        </div>
        <x-patient.appointment-table :appointments="$upcomingAppointments" empty-title="No upcoming appointments" empty-message="You have no upcoming appointments.">
            <a class="btn btn-primary btn-sm" href="{{ route('patient.appointments.create') }}">Book an Appointment</a>
        </x-patient.appointment-table>
    </section>

    <section class="card app-panel p-4 mb-4">
        <div class="app-panel-head">
            <h2 class="h5 mb-0">Recent Appointments</h2>
        </div>
        <x-patient.appointment-table :appointments="$recentAppointments" empty-title="No appointments yet" empty-message="You have no appointments yet." />
    </section>

    <section class="card app-panel p-4 mb-4">
        <h2 class="h5 mb-3">Quick Actions</h2>
        <div class="app-quick-grid">
            <x-dashboard.quick-action :href="route('patient.appointments.create')" label="Book Appointment" icon="plus" />
            <x-dashboard.quick-action :href="route('patient.appointments.index')" label="View Appointments" icon="calendar" />
            <x-dashboard.quick-action :href="route('doctors.index')" label="Find a Doctor" icon="stethoscope" />
            <x-dashboard.quick-action :href="route('patient.profile')" label="View Profile" icon="user" />
        </div>
    </section>

    <section class="card app-panel p-4">
        <div class="app-panel-head">
            <h2 class="h5 mb-0">Profile summary</h2>
            <a class="btn btn-outline-primary btn-sm" href="{{ route('patient.profile') }}">Edit profile</a>
        </div>
        <x-patient.profile-summary :patient="$patient" />
    </section>
@endsection
