<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="MediCare Hospital staff and patient portal.">
    <title>{{ $title ?? 'MediCare Hospital' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-body">
    <div class="app-shell">
        <x-dashboard.sidebar :items="$nav" :footer-items="$footerNav ?? []" />
        <div class="app-frame">
            <x-dashboard.topbar :title="$title ?? 'Dashboard'" />
            <main class="app-main">
                <x-dashboard.alerts :heading="$errorHeading ?? 'Please review the following:'" :bag="$errors" />
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
