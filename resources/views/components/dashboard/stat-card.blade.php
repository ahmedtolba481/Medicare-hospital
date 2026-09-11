@props([
    'value',
    'label',
    'icon' => 'calendar',
    'description' => null,
])

<div {{ $attributes->class(['col-6 col-md-4 col-xl-3']) }}>
    <div class="stat-card h-100">
        <span class="stat-card-icon"><x-dashboard.icon :name="$icon" /></span>
        <span class="stat-card-label">{{ $label }}</span>
        <strong class="stat-card-value">{{ $value }}</strong>
        @if ($description)
            <span class="stat-card-copy">{{ $description }}</span>
        @endif
    </div>
</div>
