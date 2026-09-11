@extends('layouts.dashboard', [
    'title' => $title ?? 'Patient area',
    'errorHeading' => 'Please correct the following fields:',
    'nav' => [
        ['label' => 'Dashboard', 'url' => route('patient.dashboard'), 'active' => request()->routeIs('patient.dashboard'), 'icon' => 'clipboard'],
        ['label' => 'My Appointments', 'url' => route('patient.appointments.index'), 'active' => request()->routeIs('patient.appointments.index', 'patient.appointments.show'), 'icon' => 'calendar'],
        ['label' => 'Book Appointment', 'url' => route('patient.appointments.create'), 'active' => request()->routeIs('patient.appointments.create'), 'icon' => 'plus'],
        ['label' => 'Doctors', 'url' => route('doctors.index'), 'active' => false, 'icon' => 'stethoscope'],
        ['label' => 'Departments', 'url' => route('departments.index'), 'active' => false, 'icon' => 'building'],
        ['label' => 'Messages', 'url' => route('patient.messages.index'), 'active' => request()->routeIs('patient.messages.*'), 'icon' => 'inbox'],
    ],
    'footerNav' => [
        ['label' => 'Profile', 'url' => route('patient.profile'), 'active' => request()->routeIs('patient.profile*'), 'icon' => 'user'],
    ],
])

@section('content')
    @yield('patient-content')
@endsection
