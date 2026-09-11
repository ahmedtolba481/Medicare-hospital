@extends('layouts.public')

@section('content')
    <x-public.page-header :title="$title ?? 'Admin area'" subtitle="Manage hospital records and contact messages." />
    <section class="section-padding">
        <div class="container">
            <div class="row g-4">
                <aside class="col-lg-3">
                    <nav class="list-group" aria-label="Admin navigation">
                        @foreach (['dashboard' => 'Dashboard', 'doctors.index' => 'Doctors', 'departments.index' => 'Departments', 'patients.index' => 'Patients', 'appointments.index' => 'Appointments', 'messages.index' => 'Messages'] as $page => $label)
                            <a class="list-group-item list-group-item-action {{ request()->routeIs('admin.'.explode('.', $page)[0].'*') ? 'active' : '' }}" href="{{ route('admin.'.$page) }}">{{ $label }}</a>
                        @endforeach
                    </nav>
                </aside>
                <div class="col-lg-9">
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <p class="mb-2">Please review the following:</p>
                            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif
                    @yield('admin-content')
                </div>
            </div>
        </div>
    </section>
@endsection
