@extends('layouts.public')

@section('content')
    <x-public.page-header title="Departments" subtitle="Specialized expertise, connected through one care team." />
    <section class="section-padding">
        <div class="container">
            <div class="row g-4">
                @forelse ($departments as $department)
                    <div class="col-md-6 col-lg-4"><x-public.department-card :department="$department" /></div>
                @empty
                    <div class="col-12"><p class="muted-copy">Department information will be available soon. Please contact our team for help finding care.</p><a class="btn btn-primary" href="{{ route('contact') }}">Contact MediCare</a></div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
