@extends('layouts.patient', ['title' => 'Appointment details'])

@section('patient-content')
    <div class="card border-0 shadow-sm p-4">
        <h2 class="h4 mb-4">Visit with <a href="{{ route('doctors.show', $appointment->doctor) }}">{{ $appointment->doctor->user->name }}</a></h2>
        <dl class="row text-break">
            <dt class="col-sm-4">Date</dt><dd class="col-sm-8">{{ $appointment->appointment_date->format('M j, Y') }}</dd>
            <dt class="col-sm-4">Time</dt><dd class="col-sm-8">{{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</dd>
            <dt class="col-sm-4">Department</dt><dd class="col-sm-8">{{ $appointment->doctor->department->name }}</dd>
            <dt class="col-sm-4">Specialization</dt><dd class="col-sm-8">{{ $appointment->doctor->specialization }}</dd>
            <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><x-patient.appointment-status :status="$appointment->status" /></dd>
            <dt class="col-sm-4">Reason for visit</dt><dd class="col-sm-8">{{ $appointment->reason ?: 'Not provided' }}</dd>
        </dl>
        @php
            $statusMessages = [
                'pending' => 'Your appointment request is awaiting confirmation from the care team.',
                'confirmed' => 'Your appointment has been confirmed by the care team.',
                'completed' => 'This appointment has been completed.',
                'cancelled' => 'This appointment has been cancelled.',
                'rejected' => 'This appointment request was not accepted. Please contact the care team for help arranging another visit.',
            ];
        @endphp
        <p class="text-secondary">{{ $statusMessages[$appointment->status] ?? '' }}</p>
        @can('cancel', $appointment)
            @if ($appointment->isCancellable())
                <details class="border rounded-3 p-3 mb-4">
                    <summary class="text-danger fw-semibold">Cancel appointment</summary>
                    <p class="mt-3">You are about to cancel this appointment. This action cannot be undone.</p>
                    <form method="POST" action="{{ route('patient.appointments.cancel', $appointment) }}">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-danger" type="submit">Confirm cancellation</button>
                    </form>
                </details>
            @else
                <p class="small text-secondary">Only future pending or confirmed appointments can be cancelled.</p>
            @endif
        @endcan
        <a class="align-self-start" href="{{ route('patient.appointments.index') }}">Back to My Appointments</a>
    </div>
@endsection
