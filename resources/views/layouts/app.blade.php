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
            --surface:#0b0b10;
            --card:#0f0f16;
            --text:#e5e7eb;
            --muted:#9ca3af;
            --border:#232534;
            --accent:#e50914;
        }
        :root[data-theme='light']{
            --bg:#f8fafc;
            --surface:#ffffff;
            --card:#ffffff;
            --text:#0f172a;
            --muted:#64748b;
            --border:#e2e8f0;
            --accent:#e50914;
        }
        body{background-color:var(--bg);color:var(--text)}
        header{background-color:var(--surface);border-bottom:1px solid var(--border)}
        header a,header .header-btn,header button,header .header-pill a{color:var(--text) !important;border-color:var(--border)}
        .home-hero{background-color:var(--bg)}
        main{background-color:var(--bg)}
        .logo-spoke{color:var(--text)}
        .logo-events{color:var(--text)}
        .app-card{background-color:var(--card);border:1px solid var(--border)}
        .app-surface{background-color:var(--surface)}
        .app-muted{color:var(--muted) !important}
        .app-accent-text{color:var(--accent) !important}
        .btn-accent{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;background-color:var(--accent);color:#fff;border:1px solid rgba(0,0,0,0)}
        .btn-surface{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;background-color:var(--surface);color:var(--text);border:1px solid var(--border)}
        :root[data-theme='dark'] .bg-white,
        :root[data-theme='dark'] .bg-slate-50,
        :root[data-theme='dark'] .bg-slate-100{background-color:var(--card) !important}
        :root[data-theme='dark'] .text-slate-700,
        :root[data-theme='dark'] .text-slate-800,
        :root[data-theme='dark'] .text-slate-900{color:var(--text) !important}
        :root[data-theme='dark'] .text-slate-600,
        :root[data-theme='dark'] .text-slate-500,
        :root[data-theme='dark'] .text-slate-400{color:var(--muted) !important}
        :root[data-theme='dark'] .border,
        :root[data-theme='dark'] .border-t,
        :root[data-theme='dark'] .border-b,
        :root[data-theme='dark'] .border-l,
        :root[data-theme='dark'] .border-r{border-color:var(--border) !important}
        .divide-y > :not([hidden]) ~ :not([hidden]){border-color:var(--border) !important}
        .badge-pill{background:rgba(255,255,255,.92);color:#0f172a;border:1px solid rgba(15,23,42,.12)}
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
