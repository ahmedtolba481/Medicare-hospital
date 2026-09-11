@props(['href', 'label', 'icon' => 'plus'])

<a {{ $attributes->class(['app-quick-action']) }} href="{{ $href }}">
    <span class="app-quick-action-icon"><x-dashboard.icon :name="$icon" /></span>
    <span>{{ $label }}</span>
</a>
