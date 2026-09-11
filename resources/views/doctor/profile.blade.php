@extends('layouts.doctor', ['title' => 'My doctor profile'])

@section('doctor-content')
    <div class="card app-panel p-4">
        <h2 class="h4 mb-3">Personal and professional information</h2>
        <div class="d-flex align-items-center gap-3 mb-4">
            <x-public.doctor-photo :doctor="$doctor" class="doctor-avatar-sm" />
            <div><strong>{{ $doctor->user->name }}</strong><span class="d-block text-secondary">{{ $doctor->specialization }}</span></div>
        </div>
        <p class="text-secondary">Department: {{ $doctor->department->name }}</p>
        <form method="POST" action="{{ route('doctor.profile.update') }}" novalidate>
            @csrf
            @method('PATCH')
            <div class="row g-3">
                @foreach (['name' => ['Name', 'text'], 'email' => ['Email', 'email'], 'phone' => ['Phone (optional)', 'tel'], 'specialization' => ['Specialization', 'text'], 'experience' => ['Years of experience', 'number'], 'education' => ['Education (optional)', 'text']] as $field => [$label, $type])
                    @php($value = old($field, in_array($field, ['name', 'email', 'phone']) ? $doctor->user->{$field} : $doctor->{$field}))
                    <div class="col-md-6">
                        <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                        <input class="form-control @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ is_scalar($value) ? $value : '' }}" @if ($field === 'experience') min="0" max="80" @endif @if (! in_array($field, ['phone', 'education'])) required @endif>
                        @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @endforeach
                <div class="col-12">
                    <label class="form-label" for="bio">Biography (optional)</label>
                    <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" rows="5" maxlength="5000">{{ is_scalar(old('bio', $doctor->bio)) ? old('bio', $doctor->bio) : '' }}</textarea>
                    @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12"><button class="btn btn-primary" type="submit">Update profile</button></div>
            </div>
        </form>
        <a class="align-self-start mt-4" href="{{ route('doctors.show', $doctor) }}">View public profile</a>
    </div>
@endsection
