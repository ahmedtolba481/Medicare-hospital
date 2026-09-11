@props([
    'appointments',
    'showReason' => false,
    'pendingActions' => false,
])

@if ($appointments->isEmpty())
    <x-dashboard.empty-state title="No appointments" message="No appointments to display." icon="calendar" />
@else
    <div class="table-responsive">
        <table class="table app-table app-table-stack align-middle">
            <caption class="visually-hidden">Appointments assigned to you</caption>
            <thead>
                <tr>
                    <th scope="col">Time</th>
                    <th scope="col">Patient</th>
                    @if ($showReason)
                        <th scope="col">Reason</th>
                    @endif
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($appointments as $appointment)
                    <tr>
                        <td class="text-nowrap" data-label="Time">
                            {{ $appointment->appointment_date->format('M j, Y') }}
                            <span class="d-block small text-secondary">{{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</span>
                        </td>
                        <td data-label="Patient"><a href="{{ route('doctor.patients.show', $appointment->patient) }}">{{ $appointment->patient->name }}</a></td>
                        @if ($showReason)
                            <td class="text-break" data-label="Reason">{{ $appointment->reason ?: 'Not provided' }}</td>
                        @endif
                        <td data-label="Status"><x-patient.appointment-status :status="$appointment->status" /></td>
                        <td data-label="Action">
                            <div class="app-actions">
                                @if ($pendingActions && $appointment->status === 'pending')
                                    <form method="POST" action="{{ route('doctor.appointments.update', $appointment) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-success btn-sm" name="action" value="accept" type="submit">Accept</button>
                                    </form>
                                    <form method="POST" action="{{ route('doctor.appointments.update', $appointment) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-outline-danger btn-sm" name="action" value="reject" type="submit">Reject</button>
                                    </form>
                                @endif
                                <a class="btn btn-outline-primary btn-sm" href="{{ route('doctor.appointments.show', $appointment) }}">View</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
