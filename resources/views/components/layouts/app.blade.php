<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="appLayout()" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name', 'PrimaERP') }}</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-white dark:bg-slate-900 font-sans antialiased" x-cloak>

    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        <livewire:layout.sidebar />

        {{-- Mobile overlay --}}
        <div x-show="mobileOpen"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileOpen = false"
             class="fixed inset-0 z-20 bg-black/50 lg:hidden">
        </div>

        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

    {{-- Header --}}
    <livewire:layout.header />

    {{-- Page content --}}
    <main class="flex-1 overflow-y-auto bg-white dark:bg-slate-900 p-6">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-6 py-3">
        <div class="flex flex-col md:flex-row items-center justify-between gap-2 text-[10px] text-slate-500 dark:text-slate-400">

            {{-- Left: Copyright --}}
            <div class="text-center md:text-left">
                © {{ date('Y') }} <span class="font-medium text-slate-600 dark:text-slate-300">Prima ERP</span>  
                Developed by <span class="font-medium text-slate-600 dark:text-slate-300">Prima Software</span>
                <span class="mx-1">•</span>
                Version <span class="font-mono">v1.0.0 (Altair)</span>
            </div>

            {{-- Right: Link --}}
            <div>
                <a href="https://primasoftware.com" target="_blank"
                   class="inline-flex items-center gap-1 text-blue-600 dark:text-blue-400 hover:underline">
                    <i class="fa-solid fa-globe text-[10px]"></i>
                    Sales & Information
                </a>
            </div>

        </div>
    </footer>

</div>

    @livewireScripts

    <script>
        function appLayout() {
            return {
                darkMode: localStorage.getItem('darkMode') === 'true',
                mobileOpen: false,
                sidebarPinned: localStorage.getItem('sidebarPinned') === 'true',
                init() {
                    window.addEventListener('toggle-dark', () => {
                        this.darkMode = !this.darkMode;
                        localStorage.setItem('darkMode', this.darkMode);
                    });
                    window.addEventListener('toggle-mobile-sidebar', () => {
                        this.mobileOpen = !this.mobileOpen;
                    });
                    window.addEventListener('toggle-pin-sidebar', () => {
                        this.sidebarPinned = !this.sidebarPinned;
                        localStorage.setItem('sidebarPinned', this.sidebarPinned);
                    });

                    // Hide loading screen
                    window.addEventListener('load', () => {
                        setTimeout(() => {
                            const ls = document.getElementById('loading-screen');
                            if (ls) { ls.classList.add('erp-loading-hide'); }
                        }, 500);
                    });
                }
            }
        }
    </script>

</body>
</html>