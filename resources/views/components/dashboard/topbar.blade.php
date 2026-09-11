@props(['title'])

@php
    $user = auth()->user();
    $initials = collect(preg_split('/\s+/', trim($user->name)))
        ->filter()
        ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
        ->take(2)
        ->implode('');
    $notificationUrl = match ($user->role) {
        'admin' => route('admin.messages.index'),
        'doctor' => route('doctor.appointments.index'),
        default => route('patient.messages.index'),
    };
    $profileUrl = match ($user->role) {
        'admin' => route('admin.profile'),
        'doctor' => route('doctor.profile'),
        default => route('patient.profile'),
    };
    $showDoctorSearch = in_array($user->role, ['patient', 'admin'], true);
@endphp

<header class="app-topbar">
    <div class="d-flex align-items-center gap-3 min-w-0">
        <button class="app-icon-button d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-controls="appSidebar" aria-label="Open menu">
            <x-dashboard.icon name="menu" />
        </button>
        <div class="min-w-0">
            <nav class="app-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route($user->dashboardRoute()) }}">Portal</a>
                <span aria-hidden="true">/</span>
                <span>{{ $title }}</span>
            </nav>
            <h1 class="app-topbar-title mb-0 text-truncate">{{ $title }}</h1>
        </div>
    </div>
    <div class="app-topbar-tools">
        @if ($showDoctorSearch)
            <form class="app-topbar-search d-none d-md-flex" method="GET" action="{{ route('doctors.index') }}" role="search">
                <x-dashboard.icon name="search" />
                <label class="visually-hidden" for="portal-doctor-search">Search doctors</label>
                <input id="portal-doctor-search" type="search" name="search" maxlength="100" placeholder="Search doctors">
            </form>
        @endif
        <a class="app-icon-button" href="{{ $notificationUrl }}" aria-label="Notifications">
            <x-dashboard.icon name="bell" />
        </a>
        <a class="app-user-chip text-decoration-none" href="{{ $profileUrl }}" aria-label="Open profile">
            <span class="app-avatar" aria-hidden="true">{{ $initials }}</span>
            <span class="d-none d-sm-block text-end min-w-0">
                <span class="d-block text-truncate fw-semibold">{{ $user->name }}</span>
                <span class="d-block small text-secondary text-capitalize">{{ $user->role }}</span>
            </span>
        </a>
    </div>
</header>
