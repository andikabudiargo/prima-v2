<?php

use App\Models\Uom;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

new class extends Component {

    public string $search         = '';
    public string $statusFilter   = '';
    public string $categoryFilter = '';
    public string $activeCard     = '';
    public int    $perPage        = 10;
    public int    $page           = 1;

    public array $columns = [
        'code'     => true,
        'name'     => true,
        'category' => true,
        'note'     => true,
        'status'   => true,
    ];

    public array $selected  = [];
    public bool  $selectAll = false;

    public bool   $showModal     = false;
    public bool   $showUpload    = false;
    public ?int   $editId        = null;
    public string $form_category = '';
    public string $form_code     = '';
    public string $form_name     = '';
    public string $form_note     = '';
    public bool   $form_status   = true;
    public $dateType = '';
    public $dateStart = null;
    public $dateEnd = null;

    public function updatedSearch(): void         { $this->page = 1; }
    public function updatedStatusFilter(): void   { $this->page = 1; }
    public function updatedCategoryFilter(): void { $this->page = 1; }
    public function updatedPerPage(): void        { $this->page = 1; }

    public function applyDateFilter($type, $start, $end)
{
    $this->dateType = $type;
    $this->dateStart = $start;
    $this->dateEnd = $end;
}

public function resetDateFilter()
{
    $this->reset(['dateType', 'dateStart', 'dateEnd']);
}

    public function filterByCard(string $card): void
    {
        if ($this->activeCard === $card) {
            $this->activeCard   = '';
            $this->statusFilter = '';
        } else {
            $this->activeCard   = $card;
            $this->statusFilter = match($card) {
                'active'   => '1',
                'inactive' => '0',
                default    => '',
            };
        }
        $this->page = 1;
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'total'    => Uom::count(),
            'active'   => Uom::where('status', true)->count(),
            'inactive' => Uom::where('status', false)->count(),
        ];
    }

    #[Computed]
    public function allUoms()
    {
        return Uom::query()
            ->when($this->search, fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('code', 'like', "%{$this->search}%")
                       ->orWhere('name', 'like', "%{$this->search}%")
                )
            )
            ->when($this->statusFilter !== '', fn($q) =>
                $q->where('status', $this->statusFilter === '1')
            )
            ->when($this->categoryFilter, fn($q) =>
                $q->where('category', $this->categoryFilter)
            )
            ->when($this->dateType, function ($q) {
    if ($this->dateStart) {
        $q->whereDate($this->dateType, '>=', $this->dateStart);
    }

    if ($this->dateEnd) {
        $q->whereDate($this->dateType, '<=', $this->dateEnd);
    }
})
            ->orderBy('category')->orderBy('name')
            ->get();
    }

    #[Computed]
    public function paginatedUoms()
    {
        if ($this->perPage === 0) return $this->allUoms;
        return $this->allUoms
            ->slice(($this->page - 1) * $this->perPage, $this->perPage)
            ->values();
    }

    #[Computed]
    public function totalPages(): int
    {
        if ($this->perPage === 0 || $this->allUoms->isEmpty()) return 1;
        return (int) ceil($this->allUoms->count() / $this->perPage);
    }

    public function updatedSelectAll(bool $val): void
    {
        $this->selected = $val
            ? $this->paginatedUoms->pluck('id')->map(fn($id) => (string)$id)->toArray()
            : [];
    }

    public function setPage(int $p): void { $this->page = max(1, min($p, $this->totalPages)); }
    public function nextPage(): void      { if ($this->page < $this->totalPages) $this->page++; }
    public function previousPage(): void { if ($this->page > 1) $this->page--; }

    public function bulkActivate(): void
    {
        Uom::whereIn('id', $this->selected)->update(['status' => true]);
        $this->selected = []; $this->selectAll = false;
        $this->dispatch('toast', type: 'success', message: count($this->selected).' UoM diaktifkan.');
    }

    public function bulkDeactivate(): void
    {
        Uom::whereIn('id', $this->selected)->update(['status' => false]);
        $this->selected = []; $this->selectAll = false;
        $this->dispatch('toast', type: 'success', message: 'UoM berhasil dinonaktifkan.');
    }

    public function saveUom(): void
    {
        $this->validate([
            'form_category' => 'required|string',
            'form_code'     => 'required|string|max:20|unique:uoms,code' . ($this->editId ? ",{$this->editId}" : ''),
            'form_name'     => 'required|string|min:2',
            'form_note'     => 'nullable|string',
        ]);

        $data = [
            'category' => $this->form_category,
            'code'     => strtoupper($this->form_code),
            'name'     => $this->form_name,
            'note'     => $this->form_note ?: null,
            'status'   => $this->form_status,
        ];

        if ($this->editId) {
            Uom::findOrFail($this->editId)->update($data);
            $msg = "UoM '{$data['name']}' berhasil diperbarui.";
        } else {
            Uom::create($data);
            $msg = "UoM '{$data['name']}' berhasil ditambahkan.";
        }

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('toast', type: 'success', message: $msg);
    }

    public function editUom(int $id): void
    {
        $uom = Uom::findOrFail($id);
        $this->editId        = $id;
        $this->form_category = $uom->category;
        $this->form_code     = $uom->code;
        $this->form_name     = $uom->name;
        $this->form_note     = $uom->note ?? '';
        $this->form_status   = $uom->status;
        $this->showModal     = true;
    }

    public function deleteUom(int $id): void
    {
        $uom = Uom::findOrFail($id);
        $name = $uom->name;
        $uom->delete();
        $this->dispatch('toast', type: 'success', message: "UoM '{$name}' berhasil dihapus.");
    }

    public function openAddModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editId        = null;
        $this->form_category = '';
        $this->form_code     = '';
        $this->form_name     = '';
        $this->form_note     = '';
        $this->form_status   = true;
        $this->resetValidation();
    }

    public function exportData(): void
    {
        $this->dispatch('toast', type: 'info', message: 'Fitur export segera hadir.');
    }

}; ?>

<div class="space-y-5">

    {{-- SweetAlert Toast listener --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('toast', ({ type, message }) => {
                const icons = { success: 'success', error: 'error', info: 'info', warning: 'warning' };
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: icons[type] ?? 'info',
                    title: message,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    customClass: { popup: 'text-sm' }
                });
            });
        });
    </script>

    {{-- Header --}}
    <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-700">
        <div>
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Material Registration</h2>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Pendaftaran material baru ke sistem</p>
        </div>
        <button type="button" wire:click="openAddModal"
                class="flex items-center gap-2 px-3 py-2 bg-[#1a3a6e] hover:bg-[#2251a3]
                       text-white text-xs font-medium rounded-lg transition-colors">
            <i class="fa-solid fa-plus"></i>
            Register Material
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-4 gap-4">

        <button type="button" wire:click="filterByCard('total')"
                @class(['text-left bg-white dark:bg-slate-800 rounded-xl border p-4 transition-all cursor-pointer',
                    'border-blue-400 ring-2 ring-blue-200 dark:ring-blue-800' => $activeCard === 'total',
                    'border-slate-200 dark:border-slate-700 hover:border-blue-300' => $activeCard !== 'total'])>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400 mb-1">Total</p>
                    <p class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ $this->stats['total'] }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-ruler-combined text-blue-500 text-lg"></i>
                </div>
            </div>
        </button>

        <button type="button" wire:click="filterByCard('active')"
                @class(['text-left bg-white dark:bg-slate-800 rounded-xl border p-4 transition-all cursor-pointer',
                    'border-green-400 ring-2 ring-green-200 dark:ring-green-800' => $activeCard === 'active',
                    'border-slate-200 dark:border-slate-700 hover:border-green-300' => $activeCard !== 'active'])>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400 mb-1">Draft</p>
                    <p class="text-2xl font-bold text-green-600">{{ $this->stats['active'] }}</p>
                </div>
                <div class="w-10 h-10 bg-green-50 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-circle-check text-green-500 text-lg"></i>
                </div>
            </div>
        </button>

        <button type="button" wire:click="filterByCard('active')"
                @class(['text-left bg-white dark:bg-slate-800 rounded-xl border p-4 transition-all cursor-pointer',
                    'border-green-400 ring-2 ring-green-200 dark:ring-green-800' => $activeCard === 'active',
                    'border-slate-200 dark:border-slate-700 hover:border-green-300' => $activeCard !== 'active'])>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400 mb-1">Submitted</p>
                    <p class="text-2xl font-bold text-green-600">{{ $this->stats['active'] }}</p>
                </div>
                <div class="w-10 h-10 bg-green-50 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-circle-check text-green-500 text-lg"></i>
                </div>
            </div>
        </button>

        <button type="button" wire:click="filterByCard('inactive')"
                @class(['text-left bg-white dark:bg-slate-800 rounded-xl border p-4 transition-all cursor-pointer',
                    'border-red-400 ring-2 ring-red-200 dark:ring-red-800' => $activeCard === 'inactive',
                    'border-slate-200 dark:border-slate-700 hover:border-red-300' => $activeCard !== 'inactive'])>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400 mb-1">Registered</p>
                    <p class="text-2xl font-bold text-red-500">{{ $this->stats['inactive'] }}</p>
                </div>
                <div class="w-10 h-10 bg-red-50 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-circle-xmark text-red-400 text-lg"></i>
                </div>
            </div>
        </button>

    </div>

    {{-- Table Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">

        {{-- Toolbar --}}
        <div class="flex flex-wrap items-center gap-2 p-4 border-b border-slate-200 dark:border-slate-700">

            {{-- Status filter --}}
            <div x-data="{ open: false }" class="relative">
                <button type="button" @click="open = !open" @click.outside="open = false"
                        class="h-9 px-3 flex items-center gap-2 border border-slate-200 dark:border-slate-600
                               bg-white dark:bg-slate-700 rounded-lg text-slate-600 dark:text-slate-300
                               hover:bg-slate-50 transition min-w-[110px]">
                    <span class="flex-1 text-left text-xs">
                        {{ $statusFilter === '' ? 'All Status' : ($statusFilter === '1' ? 'Active' : 'Non-Active') }}
                    </span>
                    <i class="fa-solid fa-chevron-down text-[9px] text-slate-400 transition-transform duration-200"
                       :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     class="absolute left-0 top-full mt-1 w-36 bg-white dark:bg-slate-800 border
                            border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-30 p-1">
                    @foreach(['' => 'All Status', '1' => 'Active', '0' => 'Non-Active'] as $val => $label)
                    <button type="button"
                            wire:click="$set('statusFilter', '{{ $val }}')"
                            @click="open = false"
                            @class(['w-full text-left px-3 py-1.5 text-xs rounded-lg transition',
                                'bg-[#1a3a6e] text-white' => $statusFilter === (string)$val,
                                'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' => $statusFilter !== (string)$val])>
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Category filter --}}
<div 
    x-data="{
        open: false,
        search: '',
        categories: [
            { val: '', label: 'All Category' },
            { val: 'Weight', label: 'Weight (Berat)' },
            { val: 'Distance', label: 'Distance (Jarak)' },
            { val: 'Volume', label: 'Volume (Ruang)' },
            { val: 'Quantity', label: 'Quantity (Jumlah)' },
            { val: 'Density', label: 'Density (Massa Jenis)' },
            { val: 'Time', label: 'Time (Waktu)' },
            { val: 'People', label: 'People (Orang)' },
            { val: 'Temperature', label: 'Temperature (Suhu)' },
            { val: 'Energy', label: 'Energy (Energi)' },
            { val: 'Power', label: 'Power (Daya)' },
            { val: 'Pressure', label: 'Pressure (Tekanan)' },
            { val: 'Speed', label: 'Speed (Kecepatan)' },
            { val: 'Percentage', label: 'Percentage (Rasio)' }
        ],
        get filtered() {
            if (!this.search) return this.categories;
            return this.categories.filter(c =>
                c.label.toLowerCase().includes(this.search.toLowerCase())
            );
        }
    }"
    class="relative"
>

    {{-- Button --}}
    <button type="button"
        @click="open = !open"
        @click.outside="open = false"
        class="h-9 px-3 flex items-center gap-2 border border-slate-200 dark:border-slate-600
               bg-white dark:bg-slate-700 rounded-lg text-slate-600 dark:text-slate-300
               hover:bg-slate-50 transition min-w-[140px]">
        
        <span class="flex-1 text-left text-xs truncate">
            {{ $categoryFilter ?: 'All Category' }}
        </span>

        <i class="fa-solid fa-chevron-down text-[9px] text-slate-400 transition-transform duration-200"
           :class="open ? 'rotate-180' : ''"></i>
    </button>

    {{-- Dropdown --}}
    <div x-show="open"
     @click.stop
         x-transition
         class="absolute left-0 top-full mt-1 w-56 bg-white dark:bg-slate-800 border
                border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-30">

        {{-- Search --}}
        <div class="p-2 border-b border-slate-200 dark:border-slate-700 sticky top-0 bg-inherit">
            <input type="text"
                x-model="search"
                 @click.stop
                placeholder="Search category..."
                class="w-full h-8 px-2 text-xs rounded-md border border-slate-200
                       dark:border-slate-600 bg-white dark:bg-slate-700
                       text-slate-600 dark:text-slate-300 focus:outline-none">
        </div>

        {{-- List --}}
        <div class="max-h-60 overflow-y-auto p-1">

            <template x-for="cat in filtered" :key="cat.val">
                <button type="button"
                    @click="
                        $wire.set('categoryFilter', cat.val);
                        open = false;
                        search = '';
                    "
                    class="w-full text-left px-3 py-1.5 text-xs rounded-lg transition"
                    :class="{
                        'bg-[#1a3a6e] text-white': '{{ $categoryFilter }}' === cat.val,
                        'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700':
                            '{{ $categoryFilter }}' !== cat.val
                    }"
                    x-text="cat.label">
                </button>
            </template>

            {{-- Empty State --}}
            <div x-show="filtered.length === 0"
                 class="text-center text-xs text-slate-400 py-3">
                No category found
            </div>

        </div>
    </div>
</div>

{{-- Date filter --}}
<div 
    x-data="{
        open: false,
        type: @entangle('dateType').defer,
        start: @entangle('dateStart').defer,
        end: @entangle('dateEnd').defer,
    }"
    class="relative"
    wire:ignore
>

    {{-- Button --}}
    <button type="button"
        @click="open = !open"
        @click.outside="
    if (!$el.contains($event.target)) {
        open = false
    }
"
        class="h-9 px-3 flex items-center gap-2 border border-slate-200 dark:border-slate-600
               bg-white dark:bg-slate-700 rounded-lg text-slate-600 dark:text-slate-300
               hover:bg-slate-50 transition min-w-[160px]">

        <span class="flex-1 text-left text-xs truncate">
            <template x-if="!type && !start && !end">
                <span>All Dates</span>
            </template>
            <template x-if="type || start || end">
                <span x-text="`${type || 'Date'} • ${start || '...'} - ${end || '...'}`"></span>
            </template>
        </span>

        <i class="fa-solid fa-calendar text-xs text-slate-400"></i>
    </button>

    {{-- Dropdown --}}
    <div x-show="open"
         x-transition
         @click.stop
          @mousedown.stop
         class="absolute left-0 top-full mt-1 w-72 bg-white dark:bg-slate-800 border
                border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-30 p-3 space-y-3">

        {{-- Type selector --}}
        <div>
            <p class="text-[10px] text-slate-400 mb-1">Date Type</p>
            <select x-model="type" @click.stop @mousedown.stop
                class="w-full h-8 text-xs rounded-md border border-slate-200 dark:border-slate-600
                       bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                
                <option value="">All</option>
                <option value="created_at">Created</option>
                <option value="submitted_at">Submitted</option>
                <option value="approved_at">Approved</option>
                <option value="rejected_at">Rejected</option>
                <option value="canceled_at">Canceled</option>
            </select>
        </div>

        {{-- Date range --}}
        <div class="grid grid-cols-2 gap-2">
            <div>
                <p class="text-[10px] text-slate-400 mb-1">Start</p>
                <input type="date" x-model="start" @click.stop @mousedown.stop :max="new Date().toISOString().split('T')[0]"
                    class="w-full h-8 text-xs rounded-md border border-slate-200 dark:border-slate-600
                           bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-300">
            </div>

            <div>
                <p class="text-[10px] text-slate-400 mb-1">End</p>
                <input type="date" x-model="end" @click.stop @mousedown.stop :max="new Date().toISOString().split('T')[0]"
                    class="w-full h-8 text-xs rounded-md border border-slate-200 dark:border-slate-600
                           bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-300">
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-between pt-2 border-t border-slate-200 dark:border-slate-700">

            {{-- Reset --}}
            <button type="button"
                @click="
                    type = '';
                    start = '';
                    end = '';
                    $wire.resetDateFilter();
                    open = false;
                "
                class="text-xs text-slate-400 hover:text-slate-600">
                Reset
            </button>

            {{-- Apply --}}
            <button type="button"
                @click="
                    $wire.applyDateFilter(type, start, end);
                    open = false;
                "
                class="px-3 py-1 text-xs bg-[#1a3a6e] text-white rounded-md">
                Apply
            </button>

        </div>
    </div>
</div>

            {{-- Search --}}
            <div class="relative flex-1 min-w-40">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari kode registrasi atau nama..."
                       class="w-full pl-8 pr-3 h-9 text-xs border border-slate-200 dark:border-slate-600
                              bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-slate-100 rounded-lg
                              outline-none focus:border-[#2251a3] focus:ring-2 focus:ring-[#2251a3]/10
                              transition placeholder-slate-400" />
            </div>

            <div class="flex items-center gap-2 ml-auto">

                {{-- Column toggle --}}
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open" @click.outside="open = false"
                            class="h-9 px-3 flex items-center gap-1.5 border border-slate-200 dark:border-slate-600
                                   bg-white dark:bg-slate-700 rounded-lg text-slate-600 dark:text-slate-300
                                   hover:bg-slate-50 transition">
                        <i class="fa-solid fa-table-columns text-xs"></i>
                        <span class="hidden sm:inline text-xs">Column</span>
                        <i class="fa-solid fa-chevron-down text-[9px] text-slate-400 transition-transform duration-200"
                           :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         class="absolute right-0 top-full mt-1 w-44 bg-white dark:bg-slate-800 border
                                border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-30 p-2">
                        @foreach(['code' => 'Code', 'name' => 'Name', 'category' => 'Category', 'note' => 'Note', 'status' => 'Status'] as $key => $label)
                        <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50
                                      dark:hover:bg-slate-700 cursor-pointer text-xs text-slate-600 dark:text-slate-300">
                            <input type="checkbox" wire:model.live="columns.{{ $key }}"
                                   class="accent-[#1a3a6e] w-3.5 h-3.5 rounded" />
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Export --}}
                <button type="button" wire:click="exportData"
                        class="h-8 px-3 flex items-center gap-1.5 text-xs border border-green-500
                               bg-green-500 hover:bg-green-600 text-white rounded-lg transition">
                    <i class="fa-solid fa-file-arrow-down"></i>
                    <span class="hidden sm:inline">Export</span>
                </button>

            </div>
        </div>

        {{-- Upload Panel --}}
        @if($showUpload)
        <div class="px-4 py-3 bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-3 flex-wrap">
                <span class="text-xs text-slate-600 dark:text-slate-300 font-medium">Bulk Upload via Excel:</span>
                <button type="button"
                        class="flex items-center gap-1.5 text-xs px-3 py-1.5 bg-green-50 text-green-700
                               border border-green-200 rounded-lg hover:bg-green-100 transition">
                    <i class="fa-solid fa-file-excel"></i> Download Template
                </button>
                <label class="flex items-center gap-1.5 text-xs px-3 py-1.5 bg-blue-50 text-blue-700
                              border border-blue-200 rounded-lg hover:bg-blue-100 transition cursor-pointer">
                    <i class="fa-solid fa-upload"></i> Pilih File
                    <input type="file" accept=".xlsx,.xls" class="hidden" />
                </label>
                <span class="text-xs text-slate-400">Format: .xlsx / .xls</span>
                <button type="button"
                        class="ml-auto text-xs px-3 py-1.5 bg-[#1a3a6e] text-white rounded-lg hover:bg-[#2251a3] transition">
                    <i class="fa-solid fa-cloud-arrow-up mr-1"></i> Proses Upload
                </button>
            </div>
        </div>
        @endif

        {{-- Bulk bar --}}
        @if(count($selected) > 0)
        <div class="flex items-center gap-3 px-4 py-2.5 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-100 dark:border-blue-800">
            <span class="text-xs font-medium text-blue-700 dark:text-blue-400">{{ count($selected) }} item dipilih</span>
            <button type="button" wire:click="bulkActivate"
                    class="flex items-center gap-1.5 text-xs px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-lg transition">
                <i class="fa-solid fa-circle-check"></i> Approve
            </button>
            <button type="button" wire:click="bulkDeactivate"
                    class="flex items-center gap-1.5 text-xs px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg transition">
                <i class="fa-solid fa-circle-xmark"></i> Reject
            </button>
            <button type="button" wire:click="$set('selected', [])"
                    class="ml-auto text-xs text-slate-400 hover:text-slate-600 transition">
                <i class="fa-solid fa-xmark"></i> Batal
            </button>
        </div>
        @endif

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50">
                        <th class="w-10 px-4 py-3">
                            <input type="checkbox" wire:model.live="selectAll" class="accent-[#1a3a6e] rounded" />
                        </th>
                        <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-10">No</th>
                        @if($columns['code'])
                        <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Code</th>
                        @endif
                        @if($columns['name'])
                        <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Name</th>
                        @endif
                        @if($columns['category'])
                        <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Category</th>
                        @endif
                        @if($columns['note'])
                        <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Note</th>
                        @endif
                        @if($columns['status'])
                        <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Status</th>
                        @endif
                        <th class="px-3 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wide w-20">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($this->paginatedUoms as $i => $uom)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors group">
                        <td class="px-4 py-2.5">
                            <input type="checkbox" wire:model.live="selected" value="{{ $uom->id }}"
                                   class="accent-[#1a3a6e] rounded" />
                        </td>
                        <td class="px-3 py-2.5 text-slate-400 text-xs">
                            {{ (($page - 1) * ($perPage ?: $this->allUoms->count())) + $i + 1 }}
                        </td>
                        @if($columns['code'])
                        <td class="px-3 py-2.5">
                            <span class="font-mono text-xs font-semibold px-2 py-1
                                         bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-md">
                                {{ $uom->code }}
                            </span>
                        </td>
                        @endif
                        @if($columns['name'])
                        <td class="px-3 py-2.5 font-medium text-xs text-slate-800 dark:text-slate-100">{{ $uom->name }}</td>
                        @endif
                        @if($columns['category'])
                        <td class="px-3 py-2.5">
                            @php
                            $catClass = match($uom->category) {
                                'Mass'     => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                'Length'   => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                'Volume'   => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400',
                                'Quantity' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                default    => 'bg-slate-100 text-slate-600',
                            };
                            @endphp
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $catClass }}">
                                {{ $uom->category }}
                            </span>
                        </td>
                        @endif
                        @if($columns['note'])
                        <td class="px-3 py-2.5 text-slate-500 dark:text-slate-400 text-xs max-w-xs truncate">
                            {{ $uom->note ?: '-' }}
                        </td>
                        @endif
                        @if($columns['status'])
                        <td class="px-3 py-2.5">
                            @if($uom->status)
                            <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full
                                         bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 font-medium">
                                <i class="fa-solid fa-circle text-[6px]"></i> Active
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full
                                         bg-red-50 dark:bg-red-900/30 text-red-500 dark:text-red-400 font-medium">
                                <i class="fa-solid fa-circle text-[6px]"></i> Non-Active
                            </span>
                            @endif
                        </td>
                        @endif
                        <td class="px-3 py-2.5">
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" wire:click="editUom({{ $uom->id }})"
                                        class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400
                                               hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </button>
                                <button type="button"
                                        x-on:click="
                                            Swal.fire({
                                                title: 'Hapus UoM?',
                                                text: 'Data \'{{ addslashes($uom->name) }}\' akan dihapus permanen.',
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonColor: '#ef4444',
                                                cancelButtonColor: '#94a3b8',
                                                confirmButtonText: 'Ya, Hapus',
                                                cancelButtonText: 'Batal',
                                                customClass: { popup: 'text-sm' }
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    $wire.deleteUom({{ $uom->id }});
                                                }
                                            })
                                        "
                                        class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400
                                               hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-14 h-14 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center">
                                    <i class="fa-solid fa-ruler-combined text-slate-300 dark:text-slate-500 text-2xl"></i>
                                </div>
                                <p class="text-slate-400 dark:text-slate-500 text-sm">Tidak ada data ditemukan</p>
                                @if($search || $statusFilter || $categoryFilter)
                                <button type="button"
                                        wire:click="$set('search',''); $set('statusFilter',''); $set('categoryFilter',''); $set('activeCard','')"
                                        class="text-xs text-blue-600 hover:underline">Reset filter</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between px-4 py-3 border-t border-slate-200 dark:border-slate-700">

            {{-- Show entries --}}
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                <span>Show</span>
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open" @click.outside="open = false"
                            class="h-8 px-2.5 flex items-center gap-1.5 border border-slate-200 dark:border-slate-600
                                   bg-white dark:bg-slate-700 rounded-lg text-slate-600 dark:text-slate-300
                                   hover:bg-slate-50 transition min-w-[60px]">
                        <span class="flex-1 text-left text-xs">{{ $perPage == 0 ? 'All' : $perPage }}</span>
                        <i class="fa-solid fa-chevron-down text-[9px] text-slate-400 transition-transform duration-200"
                           :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         class="absolute left-0 top-full mt-1 w-20 bg-white dark:bg-slate-800 border
                                border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-30 p-1">
                        @foreach([10, 25, 50, 100, 0] as $val)
                        <button type="button"
                                wire:click="$set('perPage', {{ $val }})"
                                @click="open = false"
                                @class(['w-full text-left px-3 py-1.5 text-xs rounded-lg transition',
                                    'bg-[#1a3a6e] text-white' => $perPage == $val,
                                    'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' => $perPage != $val])>
                            {{ $val == 0 ? 'All' : $val }}
                        </button>
                        @endforeach
                    </div>
                </div>
                <span>entries</span>
                <span class="text-slate-400 ml-1">— {{ $this->allUoms->count() }} total</span>
            </div>

            {{-- Pagination --}}
            @if($this->totalPages > 1)
            <div class="flex items-center gap-1">
                <button type="button" wire:click="previousPage"
                        @class(['w-8 h-8 flex items-center justify-center rounded-lg border text-xs transition',
                            'border-slate-200 dark:border-slate-600 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700' => $page > 1,
                            'border-slate-100 dark:border-slate-700 text-slate-300 cursor-not-allowed pointer-events-none' => $page <= 1])>
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                @for($p = 1; $p <= $this->totalPages; $p++)
                <button type="button" wire:click="setPage({{ $p }})"
                        @class(['w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium transition',
                            'bg-[#1a3a6e] text-white' => $page === $p,
                            'border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' => $page !== $p])>
                    {{ $p }}
                </button>
                @endfor
                <button type="button" wire:click="nextPage"
                        @class(['w-8 h-8 flex items-center justify-center rounded-lg border text-xs transition',
                            'border-slate-200 dark:border-slate-600 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700' => $page < $this->totalPages,
                            'border-slate-100 dark:border-slate-700 text-slate-300 cursor-not-allowed pointer-events-none' => $page >= $this->totalPages])>
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
            @endif

        </div>
    </div>

    {{-- ═══════════════════════════════════════════════
         MODAL — pakai $wire.entangle, BUKAN @if
    ═══════════════════════════════════════════════ --}}
    <div x-data="{ show: $wire.entangle('showModal').live }"
         x-show="show"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(0,0,0,0.5); display:none;">

        <div x-show="show"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.outside="$wire.call('closeModal')"
             class="w-full max-w-lg bg-white dark:bg-slate-800 rounded-2xl shadow-2xl overflow-hidden">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-blue-50 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-ruler-combined text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                            {{ $editId ? 'Edit UoM' : 'Add New UoM' }}
                        </h3>
                        <p class="text-xs text-slate-400">
                            {{ $editId ? 'Perbarui data satuan' : 'Tambah satuan pengukuran baru' }}
                        </p>
                    </div>
                </div>
                <button type="button" wire:click="closeModal"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400
                               hover:text-red-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="px-6 py-5 space-y-4">

                {{-- Category --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <div x-data="{ open: false }" class="relative">
                        <button type="button" @click="open = !open" @click.outside="open = false"
                                class="w-full h-10 px-3 flex items-center text-sm border border-slate-200
                                       dark:border-slate-600 bg-slate-50 dark:bg-slate-700
                                       text-slate-800 dark:text-slate-100 rounded-lg transition
                                       focus:border-[#2251a3]">
                            <span class="flex-1 text-left {{ $form_category ? '' : 'text-slate-400' }}">
                                {{ $form_category ?: 'Pilih kategori...' }}
                            </span>
                            <i class="fa-solid fa-chevron-down text-[9px] text-slate-400 transition-transform duration-200"
                               :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" x-transition
                             class="absolute left-0 top-full mt-1 w-full bg-white dark:bg-slate-800 border
                                    border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-50 p-1">
                            @foreach(['Mass', 'Length', 'Volume', 'Quantity'] as $cat)
                            <button type="button"
                                    wire:click="$set('form_category', '{{ $cat }}')"
                                    @click="open = false"
                                    @class(['w-full text-left px-3 py-2 text-sm rounded-lg transition',
                                        'bg-[#1a3a6e] text-white' => $form_category === $cat,
                                        'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' => $form_category !== $cat])>
                                {{ $cat }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @error('form_category')
                    <p class="mt-1 text-xs text-red-500"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                {{-- Code & Name --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            Code <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="form_code" type="text" placeholder="e.g. KG" maxlength="20"
                               class="w-full h-10 px-3 text-sm font-mono uppercase border border-slate-200
                                      dark:border-slate-600 bg-slate-50 dark:bg-slate-700
                                      text-slate-800 dark:text-slate-100 rounded-lg outline-none
                                      focus:border-[#2251a3] focus:ring-2 focus:ring-[#2251a3]/10 transition" />
                        @error('form_code')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="form_name" type="text" placeholder="e.g. Kilogram"
                               class="w-full h-10 px-3 text-sm border border-slate-200
                                      dark:border-slate-600 bg-slate-50 dark:bg-slate-700
                                      text-slate-800 dark:text-slate-100 rounded-lg outline-none
                                      focus:border-[#2251a3] focus:ring-2 focus:ring-[#2251a3]/10 transition" />
                        @error('form_name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Note --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Note</label>
                    <textarea wire:model="form_note" rows="2" placeholder="Deskripsi atau keterangan tambahan..."
                              class="w-full px-3 py-2.5 text-sm border border-slate-200 dark:border-slate-600
                                     bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-slate-100
                                     rounded-lg outline-none focus:border-[#2251a3] focus:ring-2
                                     focus:ring-[#2251a3]/10 transition resize-none"></textarea>
                </div>

                {{-- Status --}}
                <div @class([
    'flex items-center justify-between p-3 rounded-lg border transition',
    
    // ACTIVE
    'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800' => $form_status,
    
    // INACTIVE
    'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800' => !$form_status,
])>
    <div>
        <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
            Status
        </p>
        
        <p @class([
            'text-xs font-medium',
            'text-green-600 dark:text-green-400' => $form_status,
            'text-red-600 dark:text-red-400' => !$form_status,
        ])>
            {{ $form_status ? 'UoM akan diaktifkan' : 'UoM akan dinonaktifkan' }}
        </p>
    </div>
                    <button type="button" wire:click="$toggle('form_status')"
                            class="relative w-11 h-6 rounded-full transition-colors duration-200
                                   {{ $form_status ? 'bg-green-500' : 'bg-slate-300 dark:bg-slate-600' }}">
                        <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow
                                     transition-transform duration-200
                                     {{ $form_status ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>

            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center gap-3 px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50">
                <button type="button" wire:click="closeModal"
                        class="flex-1 h-10 text-sm font-medium border border-slate-200 dark:border-slate-600
                               text-slate-600 dark:text-slate-300 rounded-lg hover:bg-slate-300 transition">
                    Batal
                </button>
                <button type="button" wire:click="saveUom"
                        wire:loading.attr="disabled" wire:target="saveUom"
                        class="flex-1 h-10 flex items-center justify-center gap-2 text-sm font-medium
                               bg-[#1a3a6e] hover:bg-[#2251a3] text-white rounded-lg transition
                               disabled:opacity-70 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="saveUom" class="flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        {{ $editId ? 'Update UoM' : 'Simpan UoM' }}
                    </span>
                    <span wire:loading wire:target="saveUom" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 14.627 0 12 8h4a8 8 0 01-8 8v-4z"/>
                        </svg>
                        Menyimpan...
                    </span>
                </button>
            </div>

        </div>
    </div>

</div>