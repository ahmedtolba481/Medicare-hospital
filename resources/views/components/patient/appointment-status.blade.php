@props(['status'])

@php
    $styles = [
        'pending' => 'status-badge status-pending',
        'confirmed' => 'status-badge status-confirmed',
        'completed' => 'status-badge status-completed',
        'cancelled' => 'status-badge status-cancelled',
        'rejected' => 'status-badge status-rejected',
        'unread' => 'status-badge status-unread',
        'read' => 'status-badge status-read',
    ];
@endphp

<span {{ $attributes->class([$styles[$status] ?? 'status-badge status-cancelled']) }}>{{ ucfirst($status) }}</span>
