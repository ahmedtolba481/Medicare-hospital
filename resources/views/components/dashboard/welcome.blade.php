@props(['name', 'copy'])

@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
@endphp

<section class="app-welcome">
    <p class="dashboard-kicker mb-1">Welcome back</p>
    <h2 class="mb-1">{{ $greeting }}, {{ $name }}</h2>
    <p class="muted-copy mb-0">{{ $copy }}</p>
</section>
