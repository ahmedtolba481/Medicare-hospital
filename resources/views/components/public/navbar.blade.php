<div class="topbar py-2 d-none d-md-block">
    <div class="container d-flex justify-content-between">
        <span>Compassionate care, close to home.</span>
        <span>Mon–Fri: 8:00 AM–6:00 PM · (555) 010-2026</span>
    </div>
</div>
<nav class="navbar public-navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container py-2">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ route('home') }}">
            <span class="brand-mark">+</span>MediCare
            <span class="public-brand-caption d-none d-xl-inline">Hospital care, made personal</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNavigation" aria-controls="publicNavigation" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div id="publicNavigation" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">About</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('services') ? 'active' : '' }}" href="{{ route('services') }}">Services</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}" href="{{ route('departments.index') }}">Departments</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('doctors.*') ? 'active' : '' }}" href="{{ route('doctors.index') }}">Doctors</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contact</a></li>
                @guest
                    <li class="nav-item ms-lg-2"><a class="btn btn-primary btn-sm px-3" href="{{ route('login') }}">Sign in</a></li>
                @else
                    @if (auth()->user()->role === 'patient')
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('patient.*') ? 'active' : '' }}" href="{{ route('patient.dashboard') }}">My dashboard</a></li>
                    @endif
                    @if (auth()->user()->role === 'doctor')
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('doctor.*') ? 'active' : '' }}" href="{{ route('doctor.dashboard') }}">Doctor dashboard</a></li>
                    @endif
                    @if (auth()->user()->role === 'admin')
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Admin dashboard</a></li>
                    @endif
                    <li class="nav-item ms-lg-2"><form method="POST" action="{{ route('logout') }}">@csrf <button class="btn btn-outline-primary btn-sm px-3" type="submit">Sign out</button></form></li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
