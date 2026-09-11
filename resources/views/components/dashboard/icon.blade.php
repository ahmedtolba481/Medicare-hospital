@props(['name' => 'calendar'])

@php
    $paths = [
        'calendar' => 'M7 2v2M17 2v2M4 8h16M5 4h14a1 1 0 0 1 1 1v15a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1zm3 8h.01M12 12h.01M16 12h.01M8 16h.01M12 16h.01',
        'clock' => 'M12 7v5l3 2M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z',
        'users' => 'M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM20 8a3 3 0 1 1-3 3M22 21v-2a3 3 0 0 0-2-2.8',
        'building' => 'M4 21V5a1 1 0 0 1 1-1h6v17M11 21h9V9a1 1 0 0 0-1-1h-8M7 8h.01M7 12h.01M7 16h.01M15 12h.01M15 16h.01',
        'inbox' => 'M4 6h16v12H4zM4 12h4l2 3h4l2-3h4',
        'check' => 'M5 13l4 4L19 7',
        'x' => 'M6 6l12 12M18 6L6 18',
        'alert' => 'M12 9v4M12 17h.01M10.3 4.2L2.4 18a2 2 0 0 0 1.7 3h15.8a2 2 0 0 0 1.7-3L13.7 4.2a2 2 0 0 0-3.4 0z',
        'plus' => 'M12 5v14M5 12h14',
        'user' => 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z',
        'stethoscope' => 'M6 4v6a6 6 0 0 0 12 0V4M6 4H4M18 4h2M12 16v2a4 4 0 1 0 8 0v-1',
        'clipboard' => 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2',
        'bell' => 'M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0',
        'search' => 'M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16zM21 21l-4.3-4.3',
        'menu' => 'M4 7h16M4 12h16M4 17h16',
    ];
@endphp

<svg {{ $attributes->class(['app-icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <path d="{{ $paths[$name] ?? $paths['calendar'] }}" />
</svg>
