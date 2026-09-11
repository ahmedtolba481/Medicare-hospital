@extends('layouts.admin', ['title' => 'Appointment details'])
@section('admin-content')
    <section class="card border-0 shadow-sm p-4">
        <h2 class="h4 mb-3">Appointment #{{ $appointment->id }}</h2>
        <p>{{ $appointment->appointment_date->format('M j, Y') }} at {{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }} <x-patient.appointment-status :status="$appointment->status" /></p>
        <h3 class="h6">Doctor</h3>
        <p>{{ $appointment->doctor->user->name }}<br>{{ $appointment->doctor->specialization }} &middot; {{ $appointment->doctor->department->name }}</p>
        <h3 class="h6">Patient information</h3>
        <x-patient.profile-summary :patient="$appointment->patient" />
        <h3 class="h6 mt-3">Reason for visit</h3><p class="text-break">{{ $appointment->reason ?: 'Not provided' }}</p>
        <h3 class="h6">Doctor notes</h3><p class="text-break" style="white-space: pre-wrap">{{ $appointment->notes ?: 'No notes recorded.' }}</p>
        <a class="align-self-start mt-3" href="{{ route('admin.appointments.index') }}">Back to appointments</a>
    </section>
@endsection
