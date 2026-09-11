@extends('layouts.public')

@section('content')
    <x-public.page-header title="Our doctors" subtitle="Experienced physicians who bring expertise and empathy to every visit." />
    <section class="section-padding">
        <div class="container">
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <p class="mb-2">Please check your search filters.</p>
                    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            <form method="GET" action="{{ route('doctors.index') }}" class="card border-0 shadow-sm p-4 mb-5" role="search">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-5">
                        <label class="form-label" for="doctor-search">Search doctors</label>
                        <input class="form-control" id="doctor-search" type="search" name="search" value="{{ $search }}" maxlength="100" placeholder="Name, specialization, or department">
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label" for="doctor-department">Department</label>
                        <select class="form-select" id="doctor-department" name="department">
                            <option value="">All departments</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected((string) $departmentId === (string) $department->id)>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 d-flex flex-wrap gap-2">
                        <button class="btn btn-primary" type="submit">Find doctors</button>
                        <a class="btn btn-outline-primary" href="{{ route('doctors.index') }}">Clear</a>
                    </div>
                </div>
            </form>
            <p class="text-secondary mb-4">{{ $doctors->count() }} {{ $doctors->count() === 1 ? 'doctor' : 'doctors' }} found</p>
            <div class="row g-4">
                @forelse ($doctors as $doctor)
                    <div class="col-md-6 col-lg-4"><x-public.doctor-card :doctor="$doctor" /></div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 p-4">
                            <h2 class="h4">No doctors found</h2>
                            <p class="muted-copy mb-0">Try another search or department, or <a href="{{ route('contact') }}">contact our care team</a> for assistance.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
