@if (session('status'))
    <div class="container pt-3"><div class="alert alert-success alert-dismissible fade show mb-0" role="alert">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div></div>
@endif
