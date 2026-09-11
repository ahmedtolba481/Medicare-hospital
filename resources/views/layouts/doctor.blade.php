@extends('layouts.dashboard', [
    'title' => $title ?? 'Doctor area',
    'nav' => [
        ['label' => 'Dashboard', 'url' => route('doctor.dashboard'), 'active' => request()->routeIs('doctor.dashboard'), 'icon' => 'clipboard'],
        ['label' => 'Appointments', 'url' => route('doctor.appointments.index'), 'active' => request()->routeIs('doctor.appointments.*'), 'icon' => 'calendar'],
        ['label' => 'Patients', 'url' => route('doctor.patients.index'), 'active' => request()->routeIs('doctor.patients.*'), 'icon' => 'users'],
        ['label' => 'Schedule', 'url' => route('doctor.schedule.index'), 'active' => request()->routeIs('doctor.schedule.*'), 'icon' => 'clock'],
    ],
    'footerNav' => [
        ['label' => 'Profile', 'url' => route('doctor.profile'), 'active' => request()->routeIs('doctor.profile*'), 'icon' => 'user'],
    ],
])

@section('content')
    @yield('doctor-content')
@endsection
