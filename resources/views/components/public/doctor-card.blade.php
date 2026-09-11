<article class="card doctor-card overflow-hidden">
    <div class="doctor-avatar">{{ \Illuminate\Support\Str::substr($doctor->user->name, 0, 1) }}</div>
    <div class="card-body p-4"><p class="eyebrow mb-2">{{ $doctor->department->name }}</p><h3 class="h5 mb-1">{{ $doctor->user->name }}</h3><p class="text-secondary mb-4">{{ $doctor->specialization }}</p><a class="stretched-link fw-semibold text-decoration-none" href="{{ route('doctors.show', $doctor) }}">View profile →</a></div>
</article>
