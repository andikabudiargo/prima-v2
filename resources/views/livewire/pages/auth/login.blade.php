<?php
use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
{
    $this->validate();

    $this->form->authenticate();

    Session::regenerate();

    // Dispatch event ke frontend sebelum redirect
    $user = Auth::user();
    $this->dispatch('login-success', name: $user->name);

    // Delay redirect supaya toast sempat muncul
    $this->js("setTimeout(() => window.location.href = '" . route('dashboard') . "', 2000)");
}
}; ?>

<div class="min-h-screen flex font-sans bg-slate-100">

    {{-- ===== LEFT PANEL ===== --}}
    <div class="hidden lg:flex flex-1 flex-col justify-between p-12 bg-[#1a3a6e] relative overflow-hidden">

        {{-- Decorative circles --}}
        <div class="absolute -top-20 -right-20 w-80 h-80 rounded-full bg-[#2251a3] opacity-40"></div>
        <div class="absolute -bottom-16 -left-16 w-60 h-60 rounded-full bg-[#2251a3] opacity-30"></div>

        {{-- Brand --}}
        <div class="flex items-center gap-3 relative z-10">
            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shrink-0">
                <i class="fa-solid fa-industry text-[#1a3a6e] text-lg"></i>
            </div>
            <div>
                <div class="text-white text-xl font-semibold leading-tight">PrimaERP</div>
                <div class="text-[#a8c4f0] text-[10px] tracking-widest uppercase">Enterprise Resource Planning</div>
            </div>
        </div>

        {{-- Hero text --}}
        <div class="relative z-10">
            <h2 class="text-white text-3xl font-semibold leading-snug mb-4">
                Kelola operasional bisnis<br>dari satu platform terpadu
            </h2>
            <p class="text-[#a8c4f0] text-[15px] leading-relaxed max-w-sm">
                Integrasikan manufaktur, pengadaan, gudang, dan keuangan dalam satu sistem yang efisien dan real-time.
            </p>
        </div>

        {{-- Feature list --}}
        <div class="flex flex-col gap-3 relative z-10">
            @foreach([
                ['icon' => 'fa-chart-line',     'text' => 'Dashboard analitik real-time'],
                ['icon' => 'fa-boxes-stacked',  'text' => 'Manajemen inventory & gudang'],
                ['icon' => 'fa-file-invoice-dollar', 'text' => 'Akuntansi & laporan keuangan'],
                ['icon' => 'fa-gears',          'text' => 'Perencanaan & kontrol produksi'],
            ] as $f)
            <div class="flex items-center gap-3 text-[#cde0f7] text-sm">
                <div class="w-5 h-5 rounded-full bg-[#2a5cb8] flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-check text-[#7ab3ef] text-[9px]"></i>
                </div>
                {{ $f['text'] }}
            </div>
            @endforeach
        </div>
    </div>

    {{-- ===== RIGHT PANEL ===== --}}
    <div class="w-full lg:w-[480px] lg:min-w-[420px] flex flex-col justify-center px-8 py-12 sm:px-14 bg-white">

        {{-- Mobile brand --}}
        <div class="flex items-center gap-3 mb-10 lg:hidden">
            <div class="w-9 h-9 bg-[#1a3a6e] rounded-lg flex items-center justify-center shrink-0">
                <i class="fa-solid fa-industry text-white text-base"></i>
            </div>
            <div class="text-[#1a3a6e] text-lg font-semibold">PrimaERP</div>
        </div>

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-[#0f1f3d] text-2xl font-semibold mb-1.5">Selamat datang kembali</h1>
            <p class="text-[#6b7fa8] text-sm">Masuk ke akun Anda untuk mengakses sistem ERP Prima.</p>
        </div>

       <div>
    {{-- SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-5">

        {{-- Email --}}
        <div>
            <label for="email" class="block text-[13px] font-medium text-[#374467] mb-1.5">
                Alamat Email
            </label>
            <div class="relative">
                <i class="fa-regular fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-[#9baac7] text-sm pointer-events-none"></i>
                <input wire:model="form.email"
                       id="email" name="email" type="email"
                       placeholder="nama@perusahaan.com"
                       required autofocus autocomplete="username"
                       class="w-full h-[42px] pl-9 pr-4 text-sm text-[#0f1f3d] bg-slate-50 border border-slate-200 rounded-lg outline-none placeholder-slate-400
                              focus:bg-white focus:border-[#2251a3] focus:ring-2 focus:ring-[#2251a3]/10 transition" />
            </div>
            <x-input-error :messages="$errors->get('form.email')" class="mt-1.5" />
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-[13px] font-medium text-[#374467] mb-1.5">
                Password
            </label>
            <div class="relative">
                <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-[#9baac7] text-sm pointer-events-none"></i>
                <input wire:model="form.password"
                       id="password" name="password" type="password"
                       placeholder="Masukkan password Anda"
                       required autocomplete="current-password"
                       class="w-full h-[42px] pl-9 pr-10 text-sm text-[#0f1f3d] bg-slate-50 border border-slate-200 rounded-lg outline-none placeholder-slate-400
                              focus:bg-white focus:border-[#2251a3] focus:ring-2 focus:ring-[#2251a3]/10 transition" />
                <button type="button"
                        onclick="const i=document.getElementById('password'); const icon=this.querySelector('i'); i.type=i.type==='password'?'text':'password'; icon.classList.toggle('fa-eye'); icon.classList.toggle('fa-eye-slash');"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-[#9baac7] hover:text-[#6b7fa8] transition">
                    <i class="fa-regular fa-eye text-sm"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('form.password')" class="mt-1.5" />
        </div>

        {{-- Remember & Forgot --}}
        <div class="flex items-center justify-between">
            <label for="remember" class="flex items-center gap-2 text-sm text-[#6b7fa8] cursor-pointer select-none">
                <input wire:model="form.remember"
                       id="remember" type="checkbox" name="remember"
                       class="w-4 h-4 rounded border-slate-300 accent-[#2251a3] cursor-pointer" />
                Ingat saya
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" wire:navigate
                   class="text-sm text-[#2251a3] font-medium hover:underline">
                    Lupa password?
                </a>
            @endif
        </div>

        {{-- Submit Button --}}
        <button type="submit"
                wire:loading.attr="disabled"
                wire:target="login"
                class="w-full h-11 mt-1 bg-[#1a3a6e] hover:bg-[#2251a3] active:bg-[#163060]
                       disabled:opacity-70 disabled:cursor-not-allowed
                       text-white text-[15px] font-semibold rounded-lg transition
                       flex items-center justify-center gap-2">

            {{-- Normal state --}}
            <span wire:loading.remove wire:target="login" class="flex items-center gap-2">
                <i class="fa-solid fa-right-to-bracket"></i>
                Masuk ke Sistem
            </span>

            {{-- Loading state --}}
            <span wire:loading wire:target="login" class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                     fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C5.373 0 14.627 0 12 8h4a8 8 0 01-8 8v-4z"></path>
                </svg>
                Sedang masuk...
            </span>
        </button>

    </form>

    <p class="text-center text-xs text-[#9baac7] mt-8">
        Butuh akses? Hubungi
        <a href="mailto:admin@prima.co.id" class="text-[#2251a3] hover:underline">administrator sistem</a>
        Anda.
    </p>
</div>

@script
<script>
    $wire.on('login-success', (data) => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title:'Login berhasil!',
            text: 'Mengarahkan ke dashboard...',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },
            customClass: {
                popup: 'swal-toast-custom',
            }
        });
    });
</script>
@endscript