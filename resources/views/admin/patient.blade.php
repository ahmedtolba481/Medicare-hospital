@extends('layouts.admin', ['title' => 'Patient details'])
@section('admin-content')
    <section class="card app-panel p-4 mb-4">
        <h2 class="h4 mb-3">{{ $patient->name }}</h2>
        <x-patient.profile-summary :patient="$patient" />
    </section>
    <section class="card app-panel p-4">
        <h2 class="h4 mb-3">Appointment history</h2>
        <x-admin.appointment-table :appointments="$appointments" />
        {{ $appointments->links('pagination::bootstrap-5') }}
        <a class="align-self-start mt-3" href="{{ route('admin.patients.index') }}">Back to patients</a>
    </section>
@endsection
