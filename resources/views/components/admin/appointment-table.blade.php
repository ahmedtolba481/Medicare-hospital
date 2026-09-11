@props(['appointments'])
@if ($appointments->isEmpty())
    <p class="text-secondary mb-0">No appointments to display.</p>
@else
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th scope="col">Date and time</th><th scope="col">Patient</th><th scope="col">Doctor</th><th scope="col">Status</th><th scope="col">Details</th></tr></thead>
            <tbody>
                @foreach ($appointments as $appointment)
                    <tr>
                        <td class="text-nowrap">{{ $appointment->appointment_date->format('M j, Y') }}<br>{{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</td>
                        <td>{{ $appointment->patient->name }}</td>
                        <td>{{ $appointment->doctor->user->name }}</td>
                        <td><x-patient.appointment-status :status="$appointment->status" /></td>
                        <td><a href="{{ route('admin.appointments.show', $appointment) }}">View appointment</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
