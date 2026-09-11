@extends('layouts.doctor', ['title' => 'Doctor dashboard'])

@section('doctor-content')
    <h2 class="h3 mb-4">Welcome, {{ $doctor->user->name }}</h2>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4"><div class="stat-card h-100"><strong class="h2 d-block">{{ $todayAppointments->count() }}</strong><span>Today's appointments</span></div></div>
        @foreach (['pending', 'confirmed', 'completed'] as $status)
            <div class="col-6 col-md-4"><div class="stat-card h-100"><strong class="h2 d-block">{{ $statusCounts->get($status, 0) }}</strong><span>{{ ucfirst($status) }} appointments</span></div></div>
        @endforeach
        <div class="col-6 col-md-4"><div class="stat-card h-100"><strong class="h2 d-block">{{ $patientCount }}</strong><span>Patients</span></div></div>
    </div>
    <section class="card border-0 shadow-sm p-4">
        <h2 class="h4 mb-3">Today's appointments</h2>
        <p class="small text-secondary">{{ now()->format('M j, Y') }} · {{ config('app.timezone') }}</p>
        <x-doctor.appointment-table :appointments="$todayAppointments" />
        <a class="align-self-start mt-3" href="{{ route('doctor.appointments.index') }}">View all appointments</a>
    </section>
@endsection
