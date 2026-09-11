@extends('layouts.public')

@section('content')
    <x-public.page-header :title="$title ?? 'Doctor area'" subtitle="Your appointments, patients, and working schedule." />
    <section class="section-padding">
        <div class="container">
            <div class="row g-4">
                <aside class="col-lg-3">
                    <nav class="list-group" aria-label="Doctor navigation">
                        <a class="list-group-item list-group-item-action {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}" href="{{ route('doctor.dashboard') }}">Dashboard</a>
                        <a class="list-group-item list-group-item-action {{ request()->routeIs('doctor.appointments.*') ? 'active' : '' }}" href="{{ route('doctor.appointments.index') }}">Appointments</a>
                        <a class="list-group-item list-group-item-action {{ request()->routeIs('doctor.patients.*') ? 'active' : '' }}" href="{{ route('doctor.patients.index') }}">Patients</a>
                        <a class="list-group-item list-group-item-action {{ request()->routeIs('doctor.schedule.*') ? 'active' : '' }}" href="{{ route('doctor.schedule.index') }}">Schedule</a>
                        <a class="list-group-item list-group-item-action {{ request()->routeIs('doctor.profile*') ? 'active' : '' }}" href="{{ route('doctor.profile') }}">Profile</a>
                    </nav>
                </aside>
                <div class="col-lg-9">
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <p class="mb-2">Please review the following:</p>
                            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif
                    @yield('doctor-content')
                </div>
            </div>
        </div>
    </section>
@endsection
