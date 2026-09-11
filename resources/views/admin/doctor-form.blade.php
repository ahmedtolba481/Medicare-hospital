@extends('layouts.admin', ['title' => $doctor ? 'Edit doctor' : 'Create doctor'])
@section('admin-content')
    <section class="card app-panel p-4">
        <h2 class="h4 mb-3">{{ $doctor ? 'Doctor information' : 'New doctor and login account' }}</h2>
        @if ($departments->isEmpty())
            <div class="alert alert-info">Create a department before adding a doctor. <a href="{{ route('admin.departments.create') }}">Create department</a></div>
        @endif
        <form method="POST" action="{{ $doctor ? route('admin.doctors.update', $doctor) : route('admin.doctors.store') }}" enctype="multipart/form-data" novalidate>
            @csrf
            @if ($doctor) @method('PUT') @endif
            <div class="row g-3">
                <x-admin.field class="col-md-6" name="name" label="Name" :value="$doctor?->user->name" :required="true" maxlength="255" />
                <x-admin.field class="col-md-6" name="email" label="Email" type="email" :value="$doctor?->user->email" :required="true" maxlength="255" />
                <x-admin.field class="col-md-6" name="phone" label="Phone (optional)" type="tel" :value="$doctor?->user->phone" maxlength="30" />
                <div class="col-md-6">
                    <label class="form-label" for="department_id">Department</label>
                    <select class="form-select @error('department_id') is-invalid @enderror" name="department_id" id="department_id" required>
                        <option value="">Choose a department</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(is_scalar(old('department_id', $doctor?->department_id)) && (string) old('department_id', $doctor?->department_id) === (string) $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <x-admin.field class="col-md-6" name="specialization" label="Specialization" :value="$doctor?->specialization" :required="true" maxlength="255" />
                <x-admin.field class="col-md-6" name="experience" label="Years of experience" type="number" :value="$doctor?->experience ?? 0" :required="true" min="0" max="80" />
                <x-admin.field class="col-12" name="education" label="Education (optional)" :value="$doctor?->education" maxlength="255" />
                <x-admin.field class="col-12" name="bio" label="Biography (optional)" type="textarea" :value="$doctor?->bio" maxlength="5000" />
                <div class="col-md-6">
                    <label class="form-label" for="image">Profile image (optional)</label>
                    <input class="form-control @error('image') is-invalid @enderror" id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
                    <div class="form-text">JPG, PNG, or WebP up to 5 MB.</div>
                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <div class="doctor-image-preview" data-image-preview>
                        @if ($doctor?->image)
                            <img src="{{ \Illuminate\Support\Str::startsWith($doctor->image, ['https://', 'http://']) ? $doctor->image : \Illuminate\Support\Facades\Storage::disk('public')->url($doctor->image) }}" alt="Current profile image" width="120" height="90">
                        @else
                            <span class="text-secondary">No profile image selected.</span>
                        @endif
                    </div>
                </div>
                @unless ($doctor)
                    <x-admin.field class="col-md-6" name="password" label="Login password" type="password" :required="true" minlength="8" maxlength="72" autocomplete="new-password" />
                    <x-admin.field class="col-md-6" name="password_confirmation" label="Confirm password" type="password" :required="true" autocomplete="new-password" />
                    <p class="small text-secondary">Use at least 8 characters. The new account will have the doctor role.</p>
                @endunless
                <div class="col-12"><button class="btn btn-primary" type="submit">Save doctor</button></div>
            </div>
        </form>
        <a class="align-self-start mt-4" href="{{ route('admin.doctors.index') }}">Back to doctors</a>
    </section>
    <script>
        document.querySelector('#image')?.addEventListener('change', (event) => {
            const file = event.target.files[0];
            const preview = document.querySelector('[data-image-preview]');
            if (!file || !preview) return;
            const image = document.createElement('img');
            image.alt = 'Selected profile image';
            image.width = 120;
            image.height = 90;
            image.src = URL.createObjectURL(file);
            preview.replaceChildren(image);
        });
    </script>
@endsection
