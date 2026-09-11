<article class="card doctor-card overflow-hidden">
    <x-public.doctor-photo :doctor="$doctor" />
    <div class="card-body p-4">
        <p class="eyebrow mb-2">{{ $doctor->department->name }}</p>
        <h3 class="h5 mb-1">{{ $doctor->user->name }}</h3>
        <p class="text-secondary mb-2">{{ $doctor->specialization }}</p>
        <p class="text-secondary small mb-4">{{ $doctor->experience }} {{ $doctor->experience == 1 ? 'year' : 'years' }} in practice</p>
        <a class="stretched-link fw-semibold text-decoration-none" href="{{ route('doctors.show', $doctor) }}">View profile →</a>
    </div>
</article>
