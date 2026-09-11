@extends('layouts.admin', ['title' => 'Admin dashboard'])
@section('admin-content')
    <h2 class="h3 mb-4">Welcome, {{ auth()->user()->name }}</h2>
    <div class="row g-3 mb-4">
        @foreach ($statistics as $label => $count)
            <div class="col-6 col-md-4"><div class="stat-card h-100"><strong class="h2 d-block">{{ $count }}</strong><span>{{ $label }}</span></div></div>
        @endforeach
    </div>
    <section class="card border-0 shadow-sm p-4">
        <h2 class="h4 mb-3">Recently booked appointments</h2>
        <x-admin.appointment-table :appointments="$appointments" />
        <a class="align-self-start mt-3" href="{{ route('admin.appointments.index') }}">View all appointments</a>
    </section>
@endsection
