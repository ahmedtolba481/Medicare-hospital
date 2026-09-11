@props(['items', 'footerItems' => []])

@php
    $user = auth()->user();
    $roleLabel = ucfirst($user->role);
@endphp

<aside class="app-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="appSidebar" aria-labelledby="appSidebarLabel">
    <div class="app-sidebar-brand">
        <a class="app-brand text-decoration-none" href="{{ route($user->dashboardRoute()) }}">
            <span class="brand-mark">+</span>
            <span>
                <span class="d-block" id="appSidebarLabel">MediCare</span>
                <span class="app-brand-subtitle">Hospital Management</span>
            </span>
        </a>
        <span class="app-role-chip">{{ $roleLabel }}</span>
        <button class="btn-close btn-close-white d-lg-none" type="button" data-bs-dismiss="offcanvas" data-bs-target="#appSidebar" aria-label="Close menu"></button>
    </div>
    <nav class="app-sidebar-nav" aria-label="Dashboard navigation">
        <p class="app-sidebar-label">Workspace</p>
        <div class="app-sidebar-links">
            @foreach ($items as $item)
                <a class="app-nav-link {{ $item['active'] ? 'active' : '' }}" href="{{ $item['url'] }}">
                    <x-dashboard.icon :name="$item['icon'] ?? 'clipboard'" />
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
        <div class="app-sidebar-footer">
            <p class="app-sidebar-label">Account</p>
            @foreach ($footerItems as $item)
                <a class="app-nav-link {{ $item['active'] ? 'active' : '' }}" href="{{ $item['url'] }}">
                    <x-dashboard.icon :name="$item['icon'] ?? 'user'" />
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
            <a class="app-nav-link" href="{{ route('home') }}">
                <x-dashboard.icon name="building" />
                <span>Public site</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="app-nav-link app-nav-logout" type="submit">
                    <x-dashboard.icon name="x" />
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </nav>
</aside>
