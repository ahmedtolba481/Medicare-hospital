@extends('layouts.public')

@section('content')
    <x-public.page-header title="About MediCare" subtitle="Trusted medical expertise, delivered with humanity." />
    <section class="section-padding"><div class="container"><div class="row g-5 align-items-center"><div class="col-lg-6"><p class="eyebrow mb-2">Our promise</p><h2 class="display-6 mb-4">Healthcare that sees the whole person.</h2><p class="lead text-secondary">At MediCare Hospital, exceptional care begins by understanding the person behind every appointment.</p><p class="muted-copy">Our multidisciplinary team works together to provide clear guidance, modern treatment, and meaningful support for patients and their families.</p></div><div class="col-lg-6"><div class="card border-0 shadow-sm p-4 p-md-5"><div class="row g-4"><div class="col-6"><strong class="d-block h2 text-primary">{{ $departmentCount }}</strong><span class="text-secondary">specialty departments</span></div><div class="col-6"><strong class="d-block h2 text-primary">{{ $doctorCount }}</strong><span class="text-secondary">dedicated physicians</span></div><div class="col-6"><strong class="d-block h2 text-primary">1</strong><span class="text-secondary">connected care experience</span></div><div class="col-6"><strong class="d-block h2 text-primary">24/7</strong><span class="text-secondary">emergency guidance</span></div></div></div></div></div></div></section>
    <section class="section-padding bg-white"><div class="container"><div class="row g-4"><div class="col-md-4"><h2 class="h4">Our mission</h2><p class="muted-copy">To make expert, compassionate care accessible and understandable for every patient.</p></div><div class="col-md-4"><h2 class="h4">Our vision</h2><p class="muted-copy">A healthier community where every person has a trusted partner in their care.</p></div><div class="col-md-4"><h2 class="h4">Our values</h2><p class="muted-copy">Respect, clinical excellence, integrity, collaboration, and continuous learning.</p></div></div></div></section>
    <section class="section-padding">
        <div class="container">
            <div class="section-heading mb-5">
                <p class="eyebrow mb-2">Care you can trust</p>
                <h2 class="display-6">Why choose MediCare?</h2>
                <p class="muted-copy mb-0">We bring medical expertise and personal attention together, so you feel supported throughout your visit.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <article class="feature-card card p-4">
                        <h3 class="h4">A team that works together</h3>
                        <p class="muted-copy mb-0">Our physicians collaborate across specialties to understand your needs and coordinate your treatment.</p>
                    </article>
                </div>
                <div class="col-md-4">
                    <article class="feature-card card p-4">
                        <h3 class="h4">Clear, personal guidance</h3>
                        <p class="muted-copy mb-0">We listen to your questions, explain your options in plain language, and involve you in decisions about your care.</p>
                    </article>
                </div>
                <div class="col-md-4">
                    <article class="feature-card card p-4">
                        <h3 class="h4">Continuity of care</h3>
                        <p class="muted-copy mb-0">Prevention, treatment, and follow-up are part of one connected approach to supporting your wellbeing.</p>
                    </article>
                </div>
            </div>
            <a class="btn btn-primary mt-4" href="{{ route('doctors.index') }}">Meet our doctors</a>
        </div>
    </section>
@endsection
