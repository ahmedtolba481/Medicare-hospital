@extends('layouts.public')

@section('content')
    <x-public.page-header :title="$title ?? 'Patient area'" subtitle="Your appointments, profile, and care team messages." />
    <section class="section-padding">
        <div class="container">
            <div class="row g-4">
                <aside class="col-lg-3">
                    <nav class="list-group" aria-label="Patient navigation">
                        <a class="list-group-item list-group-item-action {{ request()->routeIs('patient.dashboard') ? 'active' : '' }}" href="{{ route('patient.dashboard') }}">Dashboard</a>
                        <a class="list-group-item list-group-item-action {{ request()->routeIs('patient.appointments.create') ? 'active' : '' }}" href="{{ route('patient.appointments.create') }}">Book Appointment</a>
                        <a class="list-group-item list-group-item-action {{ request()->routeIs('patient.appointments.index', 'patient.appointments.show') ? 'active' : '' }}" href="{{ route('patient.appointments.index') }}">My Appointments</a>
                        <a class="list-group-item list-group-item-action {{ request()->routeIs('patient.profile*') ? 'active' : '' }}" href="{{ route('patient.profile') }}">Profile</a>
                        <a class="list-group-item list-group-item-action {{ request()->routeIs('patient.messages.*') ? 'active' : '' }}" href="{{ route('patient.messages.index') }}">Messages</a>
                    </nav>
                </aside>
                <div class="col-lg-9">
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <p class="mb-2">Please correct the following fields:</p>
                            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif
                    @yield('patient-content')
                </div>
            </div>
        </div>
    </section>
@endsection
