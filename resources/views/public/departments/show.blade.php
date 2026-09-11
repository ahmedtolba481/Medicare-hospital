@extends('layouts.public')

@section('content')
    <x-public.page-header :title="$department->name" subtitle="Specialist care from the MediCare team." />
    <section class="section-padding">
        <div class="container">
            <a class="fw-semibold" href="{{ route('departments.index') }}">All departments</a>
            <div class="section-heading my-4">
                <h2 class="h3">About this department</h2>
                <p class="muted-copy">{{ $department->description ?: 'Contact our care team to learn more about this department.' }}</p>
            </div>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <h2 class="h3 mb-0">Doctors in {{ $department->name }}</h2>
                <a class="btn btn-outline-primary" href="{{ route('doctors.index', ['department' => $department->id]) }}">Search this department</a>
            </div>
            <div class="row g-4">
                @forelse ($department->doctors as $doctor)
                    <div class="col-md-6 col-lg-4"><x-public.doctor-card :doctor="$doctor" /></div>
                @empty
                    <div class="col-12"><p class="muted-copy">No doctor profiles are currently listed for this department. Please contact our care team for assistance.</p><a class="btn btn-primary" href="{{ route('contact') }}">Contact MediCare</a></div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
