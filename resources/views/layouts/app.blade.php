<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'BeSpoke Events' }}</title>
    <script>
        (function() {
            try {
                var saved = localStorage.getItem('theme');
                var preferred = saved ? saved : 'dark';
                document.documentElement.setAttribute('data-theme', preferred);
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    <style>
        :root[data-theme='dark']{
            --bg:#050509;
            --text:#e5e7eb;
            --accent:#e50914;
            --card:#111111;
            --border:#27272f;
            --hero-from:#000000;
            --hero-to:#111827;
        }
        :root[data-theme='light']{
            --bg:#ffffff;
            --text:#0f172a;
            --accent:#f59e0b;
            --card:#ffffff;
            --border:#e5e7eb;
            --hero-from:#ffffff;
            --hero-to:#fef3c7;
        }
        body{background-color:var(--bg);color:var(--text)}
        header{background-color:var(--card);border-bottom:1px solid var(--border)}
        header a,header .header-btn,header button,header .header-pill a{color:var(--text) !important;border-color:var(--border)}
        .home-hero{background:radial-gradient(circle at top,var(--accent) 0,rgba(0,0,0,0.85) 42%,var(--hero-to) 100%)}
        main{background-color:var(--bg)}
        .logo-spoke{color:var(--text)}
        .logo-events{color:var(--text)}
        :root[data-theme='dark'] .bg-white,
        :root[data-theme='dark'] .bg-slate-50,
        :root[data-theme='dark'] .bg-slate-100{background-color:var(--card) !important}
        :root[data-theme='dark'] .text-slate-700,
        :root[data-theme='dark'] .text-slate-600,
        :root[data-theme='dark'] .text-slate-500{color:var(--text) !important}
    </style>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        <script src="https://cdn.tailwindcss.com"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    @stack('head')
</head>
<body class="overflow-x-hidden font-sans">
    @include('partials.header')
    <main class="pt-14 pb-14 min-h-[60vh]">
        @yield('content')
    </main>
    @include('partials.footer')
    @stack('scripts')
</body>
</html>
