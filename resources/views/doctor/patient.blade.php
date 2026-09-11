@extends('layouts.doctor', ['title' => 'Patient information'])

@section('doctor-content')
    <section class="card border-0 shadow-sm p-4 mb-4">
        <h2 class="h4 mb-3">{{ $patient->name }}</h2>
        <x-patient.profile-summary :patient="$patient" />
    </section>
    <section class="card border-0 shadow-sm p-4">
        <h2 class="h4 mb-3">Appointments with you</h2>
        <x-doctor.appointment-table :appointments="$appointments" />
        {{ $appointments->links('pagination::bootstrap-5') }}
        <a class="align-self-start mt-3" href="{{ route('doctor.patients.index') }}">Back to patients</a>
    </section>
@endsection
