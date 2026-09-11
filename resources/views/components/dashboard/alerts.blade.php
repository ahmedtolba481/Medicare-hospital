@props([
    'heading' => 'Please review the following:',
    'bag',
])

@if ($bag->any())
    <div class="alert alert-danger" role="alert">
        <p class="mb-2">{{ $heading }}</p>
        <ul class="mb-0">@foreach ($bag->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
@if (session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('status') }}
        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
