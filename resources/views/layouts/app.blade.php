<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'BeSpoke Events' }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    @stack('head')
</head>
<body class="bg-white text-slate-900 overflow-x-hidden font-sans">
    @include('partials.header')
    <main class="pt-14 pb-14 min-h-[60vh]">
        @yield('content')
    </main>
    @include('partials.footer')
    @stack('scripts')
</body>
</html>
