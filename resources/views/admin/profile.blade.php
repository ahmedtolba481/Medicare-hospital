@extends('layouts.admin', ['title' => 'My profile'])

@section('admin-content')
    <div class="card app-panel p-4">
        <h2 class="h5 mb-3">Administrator account</h2>
        <form method="POST" action="{{ route('admin.profile.update') }}" novalidate>
            @csrf
            @method('PATCH')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="name">Name</label>
                    <input id="name" name="name" type="text" maxlength="255" class="form-control @error('name') is-invalid @enderror" value="{{ is_scalar(old('name', $admin->name)) ? old('name', $admin->name) : '' }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="email">Email</label>
                    <input id="email" name="email" type="email" maxlength="255" class="form-control @error('email') is-invalid @enderror" value="{{ is_scalar(old('email', $admin->email)) ? old('email', $admin->email) : '' }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="phone">Phone (optional)</label>
                    <input id="phone" name="phone" type="tel" maxlength="30" class="form-control @error('phone') is-invalid @enderror" value="{{ is_scalar(old('phone', $admin->phone)) ? old('phone', $admin->phone) : '' }}">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12"><button class="btn btn-primary" type="submit">Save profile</button></div>
            </div>
        </form>
    </div>
@endsection
