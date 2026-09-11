@extends('layouts.dashboard', [
    'title' => $title ?? 'Admin area',
    'nav' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard'), 'icon' => 'clipboard'],
        ['label' => 'Doctors', 'url' => route('admin.doctors.index'), 'active' => request()->routeIs('admin.doctors.*'), 'icon' => 'stethoscope'],
        ['label' => 'Departments', 'url' => route('admin.departments.index'), 'active' => request()->routeIs('admin.departments.*'), 'icon' => 'building'],
        ['label' => 'Patients', 'url' => route('admin.patients.index'), 'active' => request()->routeIs('admin.patients.*'), 'icon' => 'users'],
        ['label' => 'Appointments', 'url' => route('admin.appointments.index'), 'active' => request()->routeIs('admin.appointments.*'), 'icon' => 'calendar'],
        ['label' => 'Messages', 'url' => route('admin.messages.index'), 'active' => request()->routeIs('admin.messages.*'), 'icon' => 'inbox'],
    ],
    'footerNav' => [
        ['label' => 'Profile', 'url' => route('admin.profile'), 'active' => request()->routeIs('admin.profile*'), 'icon' => 'user'],
    ],
])

@section('content')
    @yield('admin-content')
@endsection
