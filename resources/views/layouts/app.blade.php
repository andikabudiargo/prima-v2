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

    {{-- Loading screen --}}
    <div id="loading-screen" class="erp-loading-screen">
        <div class="erp-loading-inner">
            <div class="erp-loading-icon">
                <i class="fa-solid fa-industry text-[#1a3a6e] text-3xl"></i>
            </div>
            <div class="erp-spinner"></div>
            <p class="erp-loading-text">Memuat sistem...</p>
        </div>
    </div>

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

        {{-- Main content --}}
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

            {{-- Header --}}
            <livewire:layout.header />

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto bg-white dark:bg-slate-900 p-6">
                {{ $slot }}
            </main>

        </div>
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