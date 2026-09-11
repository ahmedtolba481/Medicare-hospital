@extends('layouts.public')

@section('content')
    <section class="hero"><div class="container"><div class="hero-content"><p class="eyebrow mb-3">Care that starts with listening</p><h1 class="display-3 fw-bold lh-sm mb-4">Expert care for every chapter of life.</h1><p class="lead mb-4">MediCare Hospital brings experienced specialists, thoughtful treatment, and a warm human touch together in one trusted place.</p><div class="d-flex flex-wrap gap-3"><a class="btn btn-light btn-lg px-4" href="{{ route('doctors.index') }}">Meet our doctors</a><a class="btn btn-outline-light btn-lg px-4" href="{{ route('contact') }}">Contact MediCare</a></div></div></div></section>

    <section class="section-padding bg-white">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <p class="eyebrow mb-2">Welcome to MediCare</p>
                    <h2 class="display-6 mb-3">Support for every step of your care.</h2>
                    <p class="muted-copy">From your first question to your follow-up visit, our hospital brings specialists and support together. We take time to understand your concerns, explain your options, and help you plan your next steps.</p>
                    <a class="btn btn-outline-primary" href="{{ route('about') }}">About our hospital</a>
                </div>
                <div class="col-lg-6">
                    <h2 class="h3 mb-4">Our main services</h2>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-3"><h3 class="h5">Specialist consultations</h3><p class="muted-copy mb-0">Get an evaluation and a treatment plan tailored to your concerns.</p></li>
                        <li class="mb-3"><h3 class="h5">Preventive care</h3><p class="muted-copy mb-0">Plan health screenings and discuss everyday steps toward better health.</p></li>
                        <li><h3 class="h5">Follow-up support</h3><p class="muted-copy mb-0">Review your progress and understand what comes next in your care.</p></li>
                    </ul>
                    <a class="fw-semibold" href="{{ route('services') }}">Explore all services</a>
                </div>
            </div>
        </div>
    </section>

    <section class="section-padding bg-white"><div class="container"><div class="row g-4"><div class="col-md-4"><article class="feature-card card p-4"><span class="feature-icon mb-4">♥</span><h2 class="h4">Specialist expertise</h2><p class="muted-copy mb-0">A coordinated team focused on clear answers and the care that is right for you.</p></article></div><div class="col-md-4"><article class="feature-card card p-4"><span class="feature-icon mb-4">⌁</span><h2 class="h4">Whole-person care</h2><p class="muted-copy mb-0">From prevention to recovery, every decision begins with your wellbeing.</p></article></div><div class="col-md-4"><article class="feature-card card p-4"><span class="feature-icon mb-4">✓</span><h2 class="h4">Here when it matters</h2><p class="muted-copy mb-0">Clear communication, practical support, and a care team you can rely on.</p></article></div></div></div></section>

    <section class="section-padding"><div class="container"><div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-5"><div class="section-heading"><p class="eyebrow mb-2">Focused expertise</p><h2 class="display-6 mb-2">Care designed around your needs.</h2><p class="muted-copy mb-0">Explore the specialties that help our community stay well and recover with confidence.</p></div><a class="btn btn-outline-primary" href="{{ route('departments.index') }}">All departments</a></div><div class="row g-4">@forelse ($departments as $department)<div class="col-md-6 col-lg-4"><x-public.department-card :department="$department" /></div>@empty<div class="col-12"><p class="muted-copy mb-0">Department information will be available soon. Please contact our team for help finding care.</p></div>@endforelse</div></div></section>

    <section class="section-padding bg-white"><div class="container"><div class="row align-items-center g-5"><div class="col-lg-5"><p class="eyebrow mb-2">MediCare by the numbers</p><h2 class="display-6 mb-3">A trusted partner in healthier lives.</h2><p class="muted-copy mb-4">Our approach is simple: pair clinical excellence with care that feels personal.</p><a class="btn btn-primary" href="{{ route('about') }}">Discover our approach</a></div><div class="col-lg-7"><div class="row g-3"><div class="col-6"><div class="stat-card"><strong class="display-6 d-block">{{ $departmentCount }}</strong><span class="text-secondary">specialty departments</span></div></div><div class="col-6"><div class="stat-card"><strong class="display-6 d-block">{{ $doctorCount }}</strong><span class="text-secondary">experienced physicians</span></div></div><div class="col-6"><div class="stat-card"><strong class="display-6 d-block">1</strong><span class="text-secondary">connected care team</span></div></div><div class="col-6"><div class="stat-card"><strong class="display-6 d-block">100%</strong><span class="text-secondary">patient-centered focus</span></div></div></div></div></div></div></section>

    <section class="section-padding"><div class="container"><div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-5"><div class="section-heading"><p class="eyebrow mb-2">Our physicians</p><h2 class="display-6 mb-0">Meet your care team.</h2></div><a class="btn btn-outline-primary" href="{{ route('doctors.index') }}">All doctors</a></div><div class="row g-4">@forelse ($doctors as $doctor)<div class="col-md-6 col-lg-4"><x-public.doctor-card :doctor="$doctor" /></div>@empty<div class="col-12"><p class="muted-copy mb-0">Doctor profiles will be available soon. Please contact our team for assistance.</p></div>@endforelse</div></div></section>

    <section class="section-padding">
        <div class="container">
            <div class="contact-card card p-4 p-md-5">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-8">
                        <h2 class="h3 mb-3">Ready to arrange your next visit?</h2>
                        <p class="muted-copy mb-0">Choose a doctor and an available time for your visit. Your request will be sent to our care team for confirmation.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a class="btn btn-primary" href="{{ route('patient.appointments.create') }}">Book an appointment</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
