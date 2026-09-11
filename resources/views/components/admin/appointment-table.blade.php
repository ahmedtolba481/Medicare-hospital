@props(['appointments'])
@if ($appointments->isEmpty())
    <x-dashboard.empty-state title="No appointments" message="No appointments to display." icon="calendar" />
@else
    <div class="table-responsive">
        <table class="table app-table app-table-stack align-middle">
            <thead>
                <tr>
                    <th scope="col">Patient</th>
                    <th scope="col">Doctor</th>
                    <th scope="col">Date</th>
                    <th scope="col">Time</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($appointments as $appointment)
                    <tr>
                        <td data-label="Patient">{{ $appointment->patient->name }}</td>
                        <td data-label="Doctor">{{ $appointment->doctor->user->name }}</td>
                        <td class="text-nowrap" data-label="Date">{{ $appointment->appointment_date->format('M j, Y') }}</td>
                        <td class="text-nowrap" data-label="Time">{{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</td>
                        <td data-label="Status"><x-patient.appointment-status :status="$appointment->status" /></td>
                        <td data-label="Action"><a class="btn btn-outline-primary btn-sm" href="{{ route('admin.appointments.show', $appointment) }}">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
