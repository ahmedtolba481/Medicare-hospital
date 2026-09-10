{{-- Minimal login form for all hospital roles. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MediCare Hospital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 px-4 py-12 text-slate-900">
    <main class="mx-auto max-w-md rounded-lg bg-white p-8 shadow">
        <h1 class="mb-6 text-2xl font-semibold">Sign in</h1>

        @if (session('status'))
            <p class="mb-4 rounded bg-emerald-100 p-3 text-sm text-emerald-800">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="mb-1 block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="w-full rounded border-slate-300">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="mb-1 block text-sm font-medium">Password</label>
                <input id="password" name="password" type="password" required class="w-full rounded border-slate-300">
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input name="remember" type="checkbox" value="1">
                Remember me
            </label>
            <button type="submit" class="w-full rounded bg-slate-900 px-4 py-2 font-medium text-white">Sign in</button>
        </form>

        <p class="mt-6 text-sm">Need an account? <a class="font-medium underline" href="{{ route('register') }}">Register as a patient</a></p>
    </main>
</body>
</html>
