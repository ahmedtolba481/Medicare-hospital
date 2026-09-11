@extends('layouts.public', ['title' => 'Access restricted'])

@section('content')
    @php
        $dashboardUrl = auth()->check() ? route(auth()->user()->dashboardRoute()) : route('home');
        $dashboardLabel = auth()->check() ? 'Return to dashboard' : 'Return to homepage';
    @endphp

    <section class="access-page">
        <div class="container">
            <div class="access-layout">
                <div class="access-copy">
                    <span class="access-code" aria-hidden="true">403</span>
                    <p class="eyebrow mb-3">Access restricted</p>
                    <h1 class="display-5 fw-bold mb-3">This area is reserved for another care team.</h1>
                    <p class="lead muted-copy mb-4">Your account does not have permission to open this page. Use your dashboard to continue with the tools available to your role.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-primary" href="{{ $dashboardUrl }}">{{ $dashboardLabel }}</a>
                        <a class="btn btn-outline-primary" href="{{ route('home') }}">Explore MediCare</a>
                    </div>
                </div>

                <div class="access-visual" aria-hidden="true">
                    <div class="access-orbit access-orbit-one"></div>
                    <div class="access-orbit access-orbit-two"></div>
                    <div class="access-shield">
                        <span class="access-shield-cross">+</span>
                    </div>
                    <span class="access-dot access-dot-one"></span>
                    <span class="access-dot access-dot-two"></span>
                </div>
            </div>
        </div>
    </section>
@endsection
