@extends('layouts.public')

@section('content')
    <x-public.page-header title="Our doctors" subtitle="Experienced physicians who bring expertise and empathy to every visit." />
    <section class="section-padding"><div class="container"><div class="row g-4">@foreach ($doctors as $doctor)<div class="col-md-6 col-lg-4"><x-public.doctor-card :doctor="$doctor" /></div>@endforeach</div></div></section>
@endsection
