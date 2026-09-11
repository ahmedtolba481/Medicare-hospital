@extends('layouts.patient', ['title' => 'My Appointments'])

@section('patient-content')
    <div class="card app-panel p-4">
        <h2 class="h4 mb-3">Your appointment history</h2>
        <a class="btn btn-primary align-self-start mb-3" href="{{ route('patient.appointments.create') }}">Book Appointment</a>
        <x-patient.appointment-table :appointments="$appointments" />
        <div class="mt-3">{{ $appointments->links('pagination::bootstrap-5') }}</div>
    </div>
@endsection
