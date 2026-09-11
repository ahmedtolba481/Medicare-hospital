@extends('layouts.patient', ['title' => 'My profile'])

@section('patient-content')
    <div class="card app-panel p-4">
        <h2 class="h4 mb-3">Your personal information</h2>
        <form method="POST" action="{{ route('patient.profile.update') }}" novalidate>
            @csrf
            @method('PATCH')
            <div class="row g-3">
                @foreach (['name' => ['Name', 'text', 255], 'email' => ['Email', 'email', 255], 'phone' => ['Phone (optional)', 'tel', 30], 'date_of_birth' => ['Date of birth (optional)', 'date', 10]] as $field => [$label, $type, $maxLength])
                    @php($value = old($field, $field === 'date_of_birth' ? $patient->date_of_birth?->format('Y-m-d') : $patient->{$field}))
                    <div class="col-md-6">
                        <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                        <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" maxlength="{{ $maxLength }}" class="form-control @error($field) is-invalid @enderror" value="{{ is_scalar($value) ? $value : '' }}" @if (in_array($field, ['name', 'email'])) required @endif @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>
                        @error($field)<div class="invalid-feedback" id="{{ $field }}-error">{{ $message }}</div>@enderror
                    </div>
                @endforeach
                <div class="col-12">
                    <label class="form-label" for="address">Address (optional)</label>
                    <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" maxlength="1000" rows="3" @error('address') aria-invalid="true" aria-describedby="address-error" @enderror>{{ is_scalar(old('address', $patient->address)) ? old('address', $patient->address) : '' }}</textarea>
                    @error('address')<div class="invalid-feedback" id="address-error">{{ $message }}</div>@enderror
                </div>
                <div class="col-12"><button class="btn btn-primary" type="submit">Save profile</button></div>
            </div>
        </form>
    </div>
@endsection
