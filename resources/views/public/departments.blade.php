@extends('layouts.public')

@section('content')
    <x-public.page-header title="Departments" subtitle="Specialized expertise, connected through one care team." />
    <section class="section-padding"><div class="container"><div class="row g-4">@foreach ($departments as $department)<div class="col-md-6 col-lg-4"><x-public.department-card :department="$department" /></div>@endforeach</div></div></section>
@endsection
