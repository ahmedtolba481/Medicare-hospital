@extends('layouts.doctor', ['title' => 'Appointment details'])

@section('doctor-content')
    <section class="card app-panel p-4 mb-4">
        <h2 class="h4 mb-3">Visit with {{ $appointment->patient->name }}</h2>
        <p>{{ $appointment->appointment_date->format('M j, Y') }} at {{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }} · <x-patient.appointment-status :status="$appointment->status" /></p>
        <h3 class="h6">Reason for visit</h3>
        <p class="text-break">{{ $appointment->reason ?: 'Not provided' }}</p>
        <h3 class="h6">Patient information</h3>
        <x-patient.profile-summary :patient="$appointment->patient" />
    </section>
    <section class="card app-panel p-4">
        <h2 class="h4 mb-3">Appointment notes and actions</h2>
        @if (in_array($appointment->status, ['pending', 'confirmed', 'completed'], true))
            <form method="POST" action="{{ route('doctor.appointments.update', $appointment) }}" novalidate>
                @csrf
                @method('PATCH')
                <label class="form-label" for="notes">Notes (optional)</label>
                <textarea class="form-control @error('notes') is-invalid @enderror" name="notes" id="notes" rows="5" maxlength="5000" @error('notes') aria-invalid="true" aria-describedby="notes-error" @enderror>{{ is_scalar(old('notes', $appointment->notes)) ? old('notes', $appointment->notes) : '' }}</textarea>
                @error('notes')<div class="invalid-feedback" id="notes-error">{{ $message }}</div>@enderror
                <p class="small text-secondary mt-2">These notes are for the care team and are not displayed in the patient area.</p>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    @if ($appointment->status === 'pending')
                        <button class="btn btn-success" name="action" value="accept" type="submit">Accept appointment</button>
                        <button class="btn btn-danger" name="action" value="reject" type="submit">Reject appointment</button>
                    @elseif ($appointment->status === 'confirmed')
                        <button class="btn btn-success" name="action" value="complete" type="submit">Mark completed</button>
                    @endif
                    <button class="btn btn-outline-primary" name="action" value="notes" type="submit">Save notes</button>
                </div>
            </form>
        @else
            <p class="text-break">{{ $appointment->notes ?: 'No notes recorded.' }}</p>
            <p class="text-secondary">Cancelled and rejected appointments cannot be changed.</p>
        @endif
        <a class="align-self-start mt-4" href="{{ route('doctor.appointments.index') }}">Back to appointments</a>
    </section>
@endsection
