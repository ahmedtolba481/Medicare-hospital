@props(['appointments', 'emptyMessage' => 'You have no appointments yet.', 'emptyTitle' => null])

@if ($appointments->isEmpty())
    <x-dashboard.empty-state :title="$emptyTitle" :message="$emptyMessage" icon="calendar">
        {{ $slot }}
    </x-dashboard.empty-state>
@else
    <div class="table-responsive">
        <table class="table app-table app-table-stack align-middle mb-0">
            <caption class="visually-hidden">Your appointments</caption>
            <thead>
                <tr>
                    <th scope="col">Doctor</th>
                    <th scope="col">Specialization</th>
                    <th scope="col">Date</th>
                    <th scope="col">Time</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($appointments as $appointment)
                    <tr>
                        <td data-label="Doctor">{{ $appointment->doctor->user->name }}</td>
                        <td data-label="Specialization">{{ $appointment->doctor->specialization }}</td>
                        <td class="text-nowrap" data-label="Date">{{ $appointment->appointment_date->format('M j, Y') }}</td>
                        <td class="text-nowrap" data-label="Time">{{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</td>
                        <td data-label="Status"><x-patient.appointment-status :status="$appointment->status" /></td>
                        <td data-label="Action"><a class="btn btn-outline-primary btn-sm" href="{{ route('patient.appointments.show', $appointment) }}" aria-label="View appointment on {{ $appointment->appointment_date->format('M j, Y') }} with {{ $appointment->doctor->user->name }}">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
