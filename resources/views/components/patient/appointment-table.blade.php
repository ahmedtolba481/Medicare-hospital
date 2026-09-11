@props(['appointments', 'emptyMessage' => 'You have no appointments yet.'])

@if ($appointments->isEmpty())
    <p class="text-secondary mb-0">{{ $emptyMessage }}</p>
@else
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <caption class="visually-hidden">Your appointments</caption>
            <thead><tr><th scope="col">Date and time</th><th scope="col">Doctor</th><th scope="col">Status</th><th scope="col">Details</th></tr></thead>
            <tbody>
                @foreach ($appointments as $appointment)
                    <tr>
                        <td class="text-nowrap">{{ $appointment->appointment_date->format('M j, Y') }}<br><span class="small text-secondary">{{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</span></td>
                        <td>{{ $appointment->doctor->user->name }}<br><span class="small text-secondary">{{ $appointment->doctor->department->name }}</span></td>
                        <td><x-patient.appointment-status :status="$appointment->status" /></td>
                        <td><a href="{{ route('patient.appointments.show', $appointment) }}" aria-label="View appointment on {{ $appointment->appointment_date->format('M j, Y') }} with {{ $appointment->doctor->user->name }}">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
