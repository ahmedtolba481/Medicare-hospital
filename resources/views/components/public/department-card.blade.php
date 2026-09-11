<article class="card department-card p-4">
    <div class="feature-icon mb-4">✚</div>
    <h3 class="h5">{{ $department->name }}</h3>
    <p class="muted-copy mb-4">{{ $department->description }}</p>
    <div class="mt-auto d-flex justify-content-between align-items-center small"><span class="text-secondary">{{ $department->doctors_count }} care specialists</span><a class="fw-semibold text-decoration-none" href="{{ route('doctors.index') }}">Explore care →</a></div>
</article>
