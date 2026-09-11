@extends('layouts.patient', ['title' => 'Patient dashboard'])

@section('patient-content')
    <h2 class="h3 mb-2">Welcome, {{ $patient->name }}</h2>
    <p class="muted-copy mb-4">Here is an overview of your care at MediCare.</p>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4"><div class="stat-card h-100"><strong class="h2 d-block">{{ $totalAppointments }}</strong><span>Total appointments</span></div></div>
        <div class="col-6 col-md-4"><div class="stat-card h-100"><strong class="h2 d-block">{{ $upcomingCount }}</strong><span>Upcoming</span></div></div>
        @foreach (['pending', 'confirmed', 'completed', 'cancelled', 'rejected'] as $status)
            <div class="col-6 col-md-4"><div class="stat-card h-100"><strong class="h2 d-block">{{ $statusCounts->get($status, 0) }}</strong><span>{{ ucfirst($status) }}</span></div></div>
        @endforeach
    </div>
    <section class="card border-0 shadow-sm p-4 mb-4">
        <h2 class="h4 mb-3">Upcoming appointments</h2>
        <x-patient.appointment-table :appointments="$upcomingAppointments" empty-message="You have no upcoming appointments." />
        <a class="align-self-start mt-3" href="{{ route('patient.appointments.index') }}">View all appointments</a>
    </section>
    <section class="card border-0 shadow-sm p-4 mb-4">
        <h2 class="h4 mb-3">Recently added appointments</h2>
        <x-patient.appointment-table :appointments="$recentAppointments" />
    </section>
    <section class="card border-0 shadow-sm p-4">
        <h2 class="h4 mb-3">Profile summary</h2>
        <x-patient.profile-summary :patient="$patient" />
        <a class="align-self-start mt-3" href="{{ route('patient.profile') }}">Edit profile</a>
    </section>
@endsection
