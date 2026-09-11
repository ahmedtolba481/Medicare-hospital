@extends('layouts.doctor', ['title' => 'Edit working hours'])

@section('doctor-content')
    <div class="card border-0 shadow-sm p-4">
        <h2 class="h4 mb-3">Update this schedule entry</h2>
        <x-doctor.schedule-form :schedule="$schedule" />
        <a class="align-self-start mt-4" href="{{ route('doctor.schedule.index') }}">Back to schedule</a>
    </div>
@endsection
