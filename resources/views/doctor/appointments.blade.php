@extends('layouts.doctor', ['title' => 'My assigned appointments'])

@section('doctor-content')
    <div class="card app-panel p-4">
        <h2 class="h4 mb-3">Appointments</h2>
        <x-doctor.appointment-table :appointments="$appointments" />
        {{ $appointments->links('pagination::bootstrap-5') }}
    </div>
@endsection
