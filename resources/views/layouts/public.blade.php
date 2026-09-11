<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="MediCare Hospital provides compassionate, expert care for every stage of life.">
    <title>{{ $title ?? 'MediCare Hospital' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <x-public.navbar />

    <main>
        <x-public.alert />
        @yield('content')
    </main>

    <x-public.footer />
</body>
</html>
