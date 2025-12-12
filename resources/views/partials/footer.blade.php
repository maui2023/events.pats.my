<footer class="fixed bottom-0 left-0 w-full border-t bg-white">
    <div class="max-w-6xl mx-auto px-6 py-3 text-sm flex items-center justify-between">
        <nav class="flex items-center gap-4">
            <a href="{{ route('pricing') }}" class="text-slate-700">{{ __('ui.footer.pricing') }}</a>
            <a href="#" class="text-slate-700">{{ __('ui.footer.faq') }}</a>
        </nav>
        <span class="text-slate-500">© 2025 - <?=date('Y');?> BeSpoke (EMS)</span>
    </div>
</footer>
