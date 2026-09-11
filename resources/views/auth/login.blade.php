@extends('layouts.public')

@section('content')
    <section class="section-padding auth-page">
        <div class="container"><div class="row justify-content-center"><div class="col-md-8 col-lg-5"><div class="card contact-card p-4 p-md-5"><p class="eyebrow mb-2">Welcome back</p><h1 class="h2 mb-4">Sign in to MediCare</h1><form method="POST" action="{{ route('login.store') }}">@csrf <div class="mb-3"><label class="form-label" for="email">Email</label><input id="email" class="form-control @error('email') is-invalid @enderror" name="email" type="email" value="{{ old('email') }}" required autofocus>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="mb-3"><label class="form-label" for="password">Password</label><input id="password" class="form-control" name="password" type="password" required></div><div class="form-check mb-4"><input id="remember" class="form-check-input" name="remember" type="checkbox" value="1"><label class="form-check-label" for="remember">Remember me</label></div><button class="btn btn-primary w-100" type="submit">Sign in</button></form><p class="text-secondary small mt-4 mb-0">New to MediCare? <a href="{{ route('register') }}">Create a patient account</a>.</p></div></div></div></div>
    </section>
@endsection
