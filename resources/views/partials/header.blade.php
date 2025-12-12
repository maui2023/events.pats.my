<header class="fixed top-0 left-0 w-full border-b bg-white z-50">
    <style>
        .header-controls{display:flex;gap:.5rem;align-items:center}
        .header-pill{height:28px;display:inline-flex;align-items:center;border:1px solid #e5e7eb;border-radius:.375rem;overflow:hidden;font-size:13px;line-height:1}
        .header-pill a{height:100%;padding:0 12px;display:inline-flex;align-items:center}
        .header-btn{height:28px;display:inline-flex;align-items:center;padding:0 12px;border:1px solid #e5e7eb;border-radius:.375rem;background:#fff;font-size:13px;line-height:1}
    </style>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 h-12 flex items-center justify-between">
        <a href="/" class="font-semibold">BeSpoke Events</a>
        <nav class="hidden md:flex items-center gap-6 text-sm">
            <a href="/" class="text-slate-700">{{ __('ui.nav.home') }}</a>
            <a href="{{ route('events.discover') }}" class="text-slate-700">{{ __('ui.nav.events') }}</a>
            <a href="/create" class="text-slate-700">{{ __('ui.nav.create') }}</a>
            @auth
                <a href="/dashboard" class="text-slate-700">{{ __('ui.nav.dashboard') }}</a>
            @endauth
        </nav>
        <div class="header-controls">
            <div class="header-pill hidden sm:inline-flex">
                <a href="{{ route('lang.switch', 'ms') }}" class="{{ app()->getLocale()==='ms' ? 'bg-blue-50 text-blue-600' : 'text-slate-700' }}">BM</a>
                <a href="{{ route('lang.switch', 'en') }}" class="border-l {{ app()->getLocale()==='en' ? 'bg-blue-50 text-blue-600' : 'text-slate-700' }}">EN</a>
            </div>
            @auth
                <form method="POST" action="{{ route('logout') }}" id="logoutForm" class="hidden">
                    @csrf
                </form>
                <a href="#" onclick="document.getElementById('logoutForm').submit(); return false;" class="header-btn hidden sm:inline">{{ app()->getLocale()==='ms' ? 'Log Keluar' : 'Logout' }}</a>
            @else
                <a href="/login" class="header-btn hidden sm:inline">{{ app()->getLocale()==='ms' ? 'Log Masuk' : 'Login' }}</a>
            @endauth
            <button id="menuToggle" class="md:hidden px-3 py-2 border rounded text-sm">
                <span class="inline-block w-5">
                    <span class="block h-0.5 bg-slate-700 mb-1"></span>
                    <span class="block h-0.5 bg-slate-700 mb-1"></span>
                    <span class="block h-0.5 bg-slate-700"></span>
                </span>
            </button>
        </div>
    </div>
    <div id="mobileMenu" class="md:hidden hidden border-t bg-white">
        <div class="max-w-6xl mx-auto px-4 py-3 space-y-3 text-sm">
            <a href="/" class="block">{{ __('ui.nav.home') }}</a>
            <a href="{{ route('events.discover') }}" class="block">{{ __('ui.nav.events') }}</a>
            <a href="/create" class="block">{{ __('ui.nav.create') }}</a>
            @auth
                <a href="/dashboard" class="block">{{ __('ui.nav.dashboard') }}</a>
            @endauth
            <div class="inline-flex rounded border overflow-hidden text-xs">
                <a href="{{ route('lang.switch', 'ms') }}" class="px-3 py-1 {{ app()->getLocale()==='ms' ? 'bg-blue-50 text-blue-600' : 'text-slate-700' }}">BM</a>
                <a href="{{ route('lang.switch', 'en') }}" class="px-3 py-1 border-l {{ app()->getLocale()==='en' ? 'bg-blue-50 text-blue-600' : 'text-slate-700' }}">EN</a>
            </div>
            <div>
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="px-3 py-1 border rounded text-xs">{{ app()->getLocale()==='ms' ? 'Log Keluar' : 'Logout' }}</button>
                    </form>
                @else
                    <a href="/login" class="px-3 py-1 border rounded text-xs">{{ app()->getLocale()==='ms' ? 'Log Masuk' : 'Login' }}</a>
                @endauth
            </div>
        </div>
    </div>
    <script>
        const t=document.getElementById('menuToggle');
        const m=document.getElementById('mobileMenu');
        if(t&&m){t.addEventListener('click',()=>{m.classList.toggle('hidden');});}
    </script>
</header>
