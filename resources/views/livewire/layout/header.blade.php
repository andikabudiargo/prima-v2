<header class="h-10 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700
               flex items-center px-4 gap-3 flex-shrink-0 z-10 shadow-md">

    {{-- Mobile hamburger --}}
    <button @click="$dispatch('toggle-mobile-sidebar')"
            class="header-icon-btn lg:hidden">
        <i class="fa-solid fa-bars text-base"></i>
    </button>

    {{-- Pin sidebar (desktop) --}}
    <button @click="$dispatch('toggle-pin-sidebar')"
            class="header-icon-btn hidden lg:flex"
            x-data
            :title="$store.app?.sidebarPinned ? 'Unpin sidebar' : 'Pin sidebar'">
        <i class="fa-solid"
           :class="$store.app?.sidebarPinned ? 'fa-bars-staggered' : 'fa-bars'"></i>
    </button>

    {{-- Breadcrumb / Page title --}}
    <div class="flex-1 min-w-0">
        <h1 class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">
            {{ $pageTitle ?? 'Dashboard' }}
        </h1>
        @isset($breadcrumb)
        <p class="text-xs text-slate-400 dark:text-slate-500 truncate">{{ $breadcrumb }}</p>
        @endisset
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-1">

        {{-- Dark mode toggle --}}
        <button @click="$dispatch('toggle-dark')"
                class="header-icon-btn"
                :title="darkMode ? 'Mode terang' : 'Mode gelap'"
                x-data>
            <i class="fa-solid text-base"
               :class="darkMode ? 'fa-sun text-amber-400' : 'fa-moon text-slate-500'"></i>
        </button>

        {{-- Notifikasi --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="header-icon-btn relative">
                <i class="fa-regular fa-bell text-base"></i>
                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full border-2 border-white dark:border-slate-800"></span>
            </button>

            <div x-show="open" @click.outside="open = false"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="absolute right-0 top-full mt-2 w-80 bg-white dark:bg-slate-800
                        border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-50 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-slate-700">
                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">Notifikasi</span>
                    <span class="text-xs text-blue-600 cursor-pointer hover:underline">Tandai semua dibaca</span>
                </div>
                @foreach([
                    ['icon' => 'fa-cart-flatbed', 'color' => 'text-blue-500', 'bg' => 'bg-blue-50 dark:bg-blue-900/30',
                     'title' => 'PO #2041 menunggu approval', 'time' => '5 menit lalu', 'unread' => true],
                    ['icon' => 'fa-triangle-exclamation', 'color' => 'text-amber-500', 'bg' => 'bg-amber-50 dark:bg-amber-900/30',
                     'title' => 'Stok Bahan Baku A kritis', 'time' => '1 jam lalu', 'unread' => true],
                    ['icon' => 'fa-file-invoice-dollar', 'color' => 'text-green-500', 'bg' => 'bg-green-50 dark:bg-green-900/30',
                     'title' => 'Invoice INV-889 lunas', 'time' => '3 jam lalu', 'unread' => false],
                ] as $notif)
                <div class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer
                            {{ $notif['unread'] ? 'bg-blue-50/50 dark:bg-blue-900/10' : '' }}">
                    <div class="w-8 h-8 rounded-lg {{ $notif['bg'] }} flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fa-solid {{ $notif['icon'] }} {{ $notif['color'] }} text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-slate-700 dark:text-slate-200 leading-snug">{{ $notif['title'] }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $notif['time'] }}</p>
                    </div>
                    @if($notif['unread'])
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-1.5 flex-shrink-0"></div>
                    @endif
                </div>
                @endforeach
                <div class="px-4 py-2.5 border-t border-slate-100 dark:border-slate-700 text-center">
                    <a href="#" class="text-xs text-blue-600 hover:underline">Lihat semua notifikasi</a>
                </div>
            </div>
        </div>

        {{-- Profile dropdown --}}
        <div class="relative ml-1" x-data="{ open: false }">
            <button @click="open = !open"
                    class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-lg
                           hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <div class="w-7 h-7 rounded-lg bg-[#1a3a6e] flex items-center justify-center
                            text-white text-xs font-semibold flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="hidden sm:block text-left">
                    <div class="text-[10px] font-semibold text-slate-700 dark:text-slate-200 leading-tight">
                        {{ auth()->user()->name }}
                    </div>
                    <div class="text-[8px] text-slate-400 leading-tight">Administrator</div>
                </div>
                <i class="fa-solid fa-chevron-down text-[5px] text-slate-400 hidden sm:block"></i>
            </button>

            <div x-show="open" @click.outside="open = false"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="absolute right-0 top-full mt-2 w-56 bg-white dark:bg-slate-800
                        border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-50 overflow-hidden">

                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700">
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p>
                </div>

                <div class="py-1">
                    <a href="{{ route('profile') }}" wire:navigate
                       class="profile-menu-item">
                        <i class="fa-regular fa-user w-4 text-center"></i>
                        Profil Saya
                    </a>
                    <a href="#" class="profile-menu-item">
                        <i class="fa-solid fa-sliders w-4 text-center"></i>
                        Pengaturan Akun
                    </a>
                    <a href="#" class="profile-menu-item">
                        <i class="fa-regular fa-keyboard w-4 text-center"></i>
                        Pintasan Keyboard
                    </a>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-700 py-1">
                    <button wire:click="logout"
                            class="profile-menu-item w-full text-left !text-red-500 hover:!bg-red-50 dark:hover:!bg-red-900/20">
                        <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
                        Keluar
                    </button>
                </div>
            </div>
        </div>

    </div>
</header>