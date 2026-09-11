@extends('layouts.doctor', ['title' => 'My patients'])

@section('doctor-content')
    <div class="card border-0 shadow-sm p-4">
        <h2 class="h4 mb-3">Patients assigned through your appointments</h2>
        @if ($patients->isEmpty())
            <p class="text-secondary">No patients are assigned to you yet.</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th scope="col">Patient</th><th scope="col">Contact</th><th scope="col">Appointments with you</th></tr></thead>
                    <tbody>
                        @foreach ($patients as $patient)
                            <tr><td><a href="{{ route('doctor.patients.show', $patient) }}">{{ $patient->name }}</a></td><td class="text-break">{{ $patient->email }}<br>{{ $patient->phone ?: 'No phone provided' }}</td><td>{{ $patient->doctor_appointments_count }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        {{ $patients->links('pagination::bootstrap-5') }}
    </div>
@endsection
