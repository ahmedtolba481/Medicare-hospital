@props(['doctor', 'large' => false])

<div {{ $attributes->class(['doctor-avatar', 'doctor-avatar-lg' => $large]) }}>
    @if ($doctor->image)
        <img class="w-100 object-fit-cover" src="{{ \Illuminate\Support\Str::startsWith($doctor->image, ['https://', 'http://']) ? $doctor->image : \Illuminate\Support\Facades\Storage::disk('public')->url($doctor->image) }}" alt="{{ $doctor->user->name }}" width="480" height="{{ $large ? 330 : 220 }}" loading="lazy">
    @else
        <span aria-hidden="true">{{ \Illuminate\Support\Str::substr($doctor->user->name, 0, 1) }}</span>
    @endif
</div>
