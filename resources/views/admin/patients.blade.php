@extends('layouts.admin', ['title' => 'Patients'])
@section('admin-content')
    <section class="card border-0 shadow-sm p-4">
        <h2 class="h4 mb-3">Registered patients</h2>
        @if ($patients->isEmpty())
            <p>No patients have registered yet.</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th scope="col">Patient</th><th scope="col">Email</th><th scope="col">Phone</th><th scope="col">Appointments</th></tr></thead>
                    <tbody>
                        @foreach ($patients as $patient)
                            <tr><td><a href="{{ route('admin.patients.show', $patient) }}">{{ $patient->name }}</a></td><td class="text-break">{{ $patient->email }}</td><td>{{ $patient->phone ?: 'Not provided' }}</td><td>{{ $patient->appointments_count }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        {{ $patients->links('pagination::bootstrap-5') }}
    </section>
@endsection
