@extends('layouts.patient', ['title' => 'Book an appointment'])

@section('patient-content')
    <section class="card app-panel p-4 mb-4">
        <h2 class="h4 mb-3">1. Choose your doctor and date</h2>
        <p class="text-secondary">Appointments last 30 minutes. Times are shown in {{ config('app.timezone') }}.</p>
        @if ($doctors->isEmpty())
            <x-dashboard.empty-state>
                <p class="mb-0">No doctors are currently listed. Please <a href="{{ route('contact') }}">contact our care team</a> for assistance.</p>
            </x-dashboard.empty-state>
        @else
            <form method="GET" action="{{ route('patient.appointments.create') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-7">
                        <label class="form-label" for="doctor_id">Doctor</label>
                        <select id="doctor_id" name="doctor_id" class="form-select" required>
                            <option value="">Select a doctor</option>
                            @foreach ($doctors as $doctor)
                                <option value="{{ $doctor->id }}" @selected($selectedDoctor?->id === $doctor->id)>{{ $doctor->user->name }} — {{ $doctor->specialization }} ({{ $doctor->department->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="appointment_date">Date</label>
                        <input id="appointment_date" name="appointment_date" type="date" class="form-control" value="{{ $selectedDate }}" min="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="col-12"><button class="btn btn-primary" type="submit">View available times</button></div>
                </div>
            </form>
        @endif
    </section>

    @if ($selectedDoctor && $selectedDate !== '')
        <section class="card app-panel p-4">
            <h2 class="h4 mb-3">2. Choose a time and tell us about your visit</h2>
            <p>{{ $selectedDoctor->user->name }} · {{ \Illuminate\Support\Carbon::parse($selectedDate)->format('M j, Y') }}</p>
            @if ($slots === [])
                <div class="alert alert-info mb-0" role="status">No available times for this doctor on this date. Please choose another date or doctor.</div>
            @else
                <div class="d-flex align-items-center gap-3 mb-3">
                    <x-public.doctor-photo :doctor="$selectedDoctor" class="doctor-avatar-sm" />
                    <div><strong>{{ $selectedDoctor->user->name }}</strong><span class="d-block text-secondary">{{ $selectedDoctor->specialization }}</span></div>
                </div>
                <form method="POST" action="{{ route('patient.appointments.store') }}" novalidate>
                    @csrf
                    <input type="hidden" name="doctor_id" value="{{ $selectedDoctor->id }}">
                    <input type="hidden" name="appointment_date" value="{{ $selectedDate }}">
                    <fieldset class="mb-4">
                        <legend class="form-label">Available times</legend>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($slots as $slot)
                                <input class="btn-check" type="radio" name="appointment_time" id="slot-{{ $loop->index }}" value="{{ $slot }}" @checked(old('appointment_time') === $slot) required @error('appointment_time') aria-invalid="true" aria-describedby="time-error" @enderror>
                                <label class="btn btn-outline-primary" for="slot-{{ $loop->index }}">{{ $slot }}</label>
                            @endforeach
                        </div>
                        @error('appointment_time')<p id="time-error" class="text-danger mt-2 mb-0">{{ $message }}</p>@enderror
                    </fieldset>
                    <div class="mb-3">
                        <label class="form-label" for="reason">Reason for your visit</label>
                        <textarea id="reason" name="reason" rows="4" maxlength="255" class="form-control @error('reason') is-invalid @enderror" required @error('reason') aria-invalid="true" aria-describedby="reason-error" @enderror>{{ is_scalar(old('reason')) ? old('reason') : '' }}</textarea>
                        @error('reason')<div id="reason-error" class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <p class="small text-secondary">Your appointment will be pending until the care team confirms it.</p>
                    <button class="btn btn-primary" type="submit">Submit booking</button>
                </form>
            @endif
        </section>
    @endif
@endsection
