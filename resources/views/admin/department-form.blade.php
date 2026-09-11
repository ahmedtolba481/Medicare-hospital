@extends('layouts.admin', ['title' => $department ? 'Edit department' : 'Create department'])
@section('admin-content')
    <section class="card border-0 shadow-sm p-4">
        <h2 class="h4 mb-3">Department information</h2>
        <form method="POST" action="{{ $department ? route('admin.departments.update', $department) : route('admin.departments.store') }}" novalidate>
            @csrf
            @if ($department) @method('PUT') @endif
            <x-admin.field class="mb-3" name="name" label="Name" :value="$department?->name" :required="true" maxlength="255" />
            <x-admin.field class="mb-3" name="description" label="Description (optional)" type="textarea" :value="$department?->description" maxlength="5000" />
            <button class="btn btn-primary" type="submit">Save department</button>
        </form>
        <a class="align-self-start mt-4" href="{{ route('admin.departments.index') }}">Back to departments</a>
    </section>
@endsection
