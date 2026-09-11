@props([
    'title' => null,
    'message' => null,
    'icon' => 'clipboard',
])

<div {{ $attributes->class(['app-empty']) }}>
    <span class="app-empty-icon"><x-dashboard.icon :name="$icon" /></span>
    @if ($title)
        <p class="app-empty-title">{{ $title }}</p>
    @endif
    @if ($message)
        <p class="app-empty-copy {{ $slot->isEmpty() ? 'mb-0' : '' }}">{{ $message }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="app-empty-action">{{ $slot }}</div>
    @endif
</div>
