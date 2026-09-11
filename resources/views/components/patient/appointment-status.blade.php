@props(['status'])

@php
    $styles = [
        'pending' => 'text-bg-warning',
        'confirmed' => 'text-bg-primary',
        'completed' => 'text-bg-success',
        'cancelled' => 'text-bg-secondary',
        'rejected' => 'text-bg-danger',
    ];
@endphp

<span {{ $attributes->class(['badge', $styles[$status] ?? 'text-bg-secondary']) }}>{{ ucfirst($status) }}</span>
