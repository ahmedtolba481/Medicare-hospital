@extends('layouts.public')

@section('content')
    <x-public.page-header :title="$doctor->user->name" :subtitle="$doctor->specialization.' · '.$doctor->department->name" />
    <section class="section-padding">
        <div class="container">
            <a class="fw-semibold d-inline-block mb-4" href="{{ route('doctors.index') }}">All doctors</a>
            <div class="row g-5">
                <div class="col-lg-4"><x-public.doctor-photo :doctor="$doctor" :large="true" /></div>
                <div class="col-lg-8">
                    <p class="eyebrow mb-2">About {{ $doctor->user->name }}</p>
                    <h2 class="h3 mb-3">Personalized expertise for your health.</h2>
                    <p class="muted-copy">{{ $doctor->bio ?: 'Contact our care team to learn more about this doctor.' }}</p>
                    <dl class="row g-3 mt-2">
                        <div class="col-md-6"><div class="border rounded-3 p-3 h-100"><dt class="text-secondary small mb-1">Specialization</dt><dd class="fw-semibold mb-0">{{ $doctor->specialization }}</dd></div></div>
                        <div class="col-md-6"><div class="border rounded-3 p-3 h-100"><dt class="text-secondary small mb-1">Department</dt><dd class="mb-0"><a class="fw-semibold" href="{{ route('departments.show', $doctor->department) }}">{{ $doctor->department->name }}</a></dd></div></div>
                        <div class="col-md-6"><div class="border rounded-3 p-3 h-100"><dt class="text-secondary small mb-1">Experience</dt><dd class="fw-semibold mb-0">{{ $doctor->experience }} {{ $doctor->experience == 1 ? 'year' : 'years' }} in practice</dd></div></div>
                        <div class="col-md-6"><div class="border rounded-3 p-3 h-100"><dt class="text-secondary small mb-1">Education</dt><dd class="fw-semibold mb-0">{{ $doctor->education ?: 'Please contact our care team for details.' }}</dd></div></div>
                    </dl>
                    <div class="mt-4">
                        <h3 class="h5">Consultation hours</h3>
                        <p class="muted-copy">These are regular consultation hours. Contact our care team to confirm availability for your visit.</p>
                        @if ($doctor->schedules->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table">
                                    <caption class="visually-hidden">Weekly consultation schedule for {{ $doctor->user->name }}</caption>
                                    <thead><tr><th scope="col">Day</th><th scope="col">From</th><th scope="col">To</th></tr></thead>
                                    <tbody>
                                        @foreach ($doctor->schedules as $schedule)
                                            <tr><th scope="row">{{ $schedule->day }}</th><td>{{ \Illuminate\Support\Carbon::parse($schedule->start_time)->format('g:i A') }}</td><td>{{ \Illuminate\Support\Carbon::parse($schedule->end_time)->format('g:i A') }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-secondary">Please contact MediCare for availability.</p>
                        @endif
                    </div>
                    <a class="btn btn-primary mt-4" href="{{ route('patient.appointments.create', ['doctor_id' => $doctor->id]) }}">Book Appointment</a>
                    <p class="small text-secondary mt-2">Sign in as a patient to choose a date and available time.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
