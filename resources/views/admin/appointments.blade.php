@extends('layouts.admin', ['title' => 'Appointments'])
@section('admin-content')
    <section class="card border-0 shadow-sm p-4">
        <h2 class="h4 mb-3">All appointments</h2>
        <form class="row g-3 mb-4" method="GET" action="{{ route('admin.appointments.index') }}">
            <div class="col-md-4">
                <label class="form-label" for="status">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All statuses</option>
                    @foreach (['pending', 'confirmed', 'completed', 'cancelled', 'rejected'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="doctor_id">Doctor</label>
                <select class="form-select" id="doctor_id" name="doctor_id">
                    <option value="">All doctors</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected((string) ($filters['doctor_id'] ?? '') === (string) $doctor->id)>{{ $doctor->user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4"><label class="form-label" for="date">Date</label><input class="form-control" name="date" id="date" type="date" value="{{ $filters['date'] ?? '' }}"></div>
            <div class="col-12 d-flex gap-2"><button class="btn btn-primary" type="submit">Apply filters</button><a class="btn btn-outline-secondary" href="{{ route('admin.appointments.index') }}">Reset</a></div>
        </form>
        <x-admin.appointment-table :appointments="$appointments" />
        {{ $appointments->links('pagination::bootstrap-5') }}
    </section>
@endsection
