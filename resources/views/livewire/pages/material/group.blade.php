<?php

use App\Models\MaterialGroup;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

new class extends Component {

    // ── Filters ──────────────────────────────────────────
    public string $search        = '';
    public string $statusFilter  = '';
    public string $categoryFilter = '';
    public string $activeCard    = ''; // 'total' | 'active' | 'inactive'
    public int    $perPage       = 10;
    public int    $page          = 1;

    // ── Columns ──────────────────────────────────────────
    public array $columns = [
        'code'     => true,
        'name'     => true,
        'note'     => true,
        'status'   => true,
    ];

    // ── Bulk ─────────────────────────────────────────────
    public array $selected  = [];
    public bool  $selectAll = false;

    // ── Modal ─────────────────────────────────────────────
    public bool   $showModal     = false;
    public bool   $showUpload    = false;
    public ?int   $editId        = null;
    public string $form_category = '';
    public string $form_code     = '';
    public string $form_name     = '';
    public string $form_note     = '';
    public bool   $form_status   = true;

    // ── Reset page on filter change ───────────────────────
    public function updatedSearch(): void          { $this->page = 1; }
    public function updatedStatusFilter(): void    { $this->page = 1; }
    public function updatedCategoryFilter(): void  { $this->page = 1; }
    public function updatedPerPage(): void         { $this->page = 1; }

    // ── Card filter click ─────────────────────────────────
    public function filterByCard(string $card): void
    {
        if ($this->activeCard === $card) {
            // toggle off
            $this->activeCard   = '';
            $this->statusFilter = '';
        } else {
            $this->activeCard = $card;
            $this->statusFilter = match($card) {
                'active'   => '1',
                'inactive' => '0',
                default    => '',
            };
        }
        $this->page = 1;
    }

    // ── Stats (selalu dari semua data, tanpa filter) ──────
    #[Computed]
    public function stats(): array
    {
        $total    = MaterialGroup::count();
        $active   = MaterialGroup::where('status', true)->count();
        $inactive = MaterialGroup::where('status', false)->count();
        return compact('total', 'active', 'inactive');
    }

    // ── Filtered + paginated data ─────────────────────────
    #[Computed]
    public function allGroups()
    {
        return MaterialGroup::query()
            ->when($this->search, fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('code', 'like', "%{$this->search}%")
                       ->orWhere('name', 'like', "%{$this->search}%")
                )
            )
            ->when($this->statusFilter !== '', fn($q) =>
                $q->where('status', $this->statusFilter === '1')
            )
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function paginatedGroups()
    {
        if ($this->perPage === 0) return $this->allGroups;
        return $this->allGroups
            ->slice(($this->page - 1) * $this->perPage, $this->perPage)
            ->values();
    }

    #[Computed]
    public function totalPages(): int
    {
        if ($this->perPage === 0 || $this->allGroups->isEmpty()) return 1;
        return (int) ceil($this->allGroups->count() / $this->perPage);
    }

    // ── Select all ────────────────────────────────────────
    public function updatedSelectAll(bool $val): void
    {
        $this->selected = $val
            ? $this->paginatedGroups->pluck('id')->map(fn($id) => (string)$id)->toArray()
            : [];
    }

    // ── Pagination ────────────────────────────────────────
    public function setPage(int $p): void  { $this->page = max(1, min($p, $this->totalPages)); }
    public function nextPage(): void       { if ($this->page < $this->totalPages) $this->page++; }
    public function previousPage(): void  { if ($this->page > 1) $this->page--; }

    // ── Bulk actions ──────────────────────────────────────
    public function bulkActivate(): void
    {
        MaterialGroup::whereIn('id', $this->selected)->update(['status' => true]);
        $this->selected = []; $this->selectAll = false;
        session()->flash('success', 'Group berhasil diaktifkan.');
    }

    public function bulkDeactivate(): void
    {
        MaterialGroup::whereIn('id', $this->selected)->update(['status' => false]);
        $this->selected = []; $this->selectAll = false;
        session()->flash('success', 'Group berhasil dinonaktifkan.');
    }

    // ── Save (create/update) ──────────────────────────────
    public function saveGroup(): void
    {
        $rules = [
            'form_category' => 'required|string',
            'form_code'     => 'required|string|max:20|unique:group,code' . ($this->editId ? ",{$this->editId}" : ''),
            'form_name'     => 'required|string|min:2',
            'form_note'     => 'nullable|string',
        ];

        $this->validate($rules);

        $data = [
            'category' => $this->form_category,
            'code'     => strtoupper($this->form_code),
            'name'     => $this->form_name,
            'note'     => $this->form_note,
            'status'   => $this->form_status,
        ];

        if ($this->editId) {
            MaterialGroup::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Group berhasil diperbarui.');
        } else {
            MaterialGroup::create($data);
            session()->flash('success', 'Group berhasil ditambahkan.');
        }

        $this->resetForm();
        $this->showModal = false;
    }

    // ── Edit ──────────────────────────────────────────────
    public function editGroup(int $id): void
    {
        $group = MaterialGroup::findOrFail($id);
        $this->editId        = $id;
        $this->form_code     = $group->code;
        $this->form_name     = $group->name;
        $this->form_note     = $group->note ?? '';
        $this->form_status   = $group->status;
        $this->showModal     = true;
    }

    // ── Delete ────────────────────────────────────────────
    public function deleteGroup(int $id): void
    {
        MaterialGroup::findOrFail($id)->delete();
        session()->flash('success', 'Group berhasil dihapus.');
    }

    // ── Reset form ────────────────────────────────────────
    public function resetForm(): void
    {
        $this->editId        = null;
        $this->form_code     = '';
        $this->form_name     = '';
        $this->form_note     = '';
        $this->form_status   = true;
        $this->resetValidation();
    }

    public function downloadTemplate(): void
    {
        // TODO: implement Excel download
        session()->flash('info', 'Template sedang diunduh.');
    }

    public function exportData(): void
    {
        // TODO: implement Excel export
        session()->flash('info', 'Data sedang diekspor.');
    }

}; ?>

<div class="space-y-5">

    {{-- Flash --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-end="opacity-0 -translate-y-1"
         class="flex items-center gap-3 px-4 py-3 bg-green-50 dark:bg-green-900/20
                border border-green-200 dark:border-green-800 rounded-xl text-green-700
                dark:text-green-400 text-sm">
        <i class="fa-solid fa-circle-check text-green-500"></i>
        {{ session('success') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-700">
        <div>
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Material Group</h2>
            <p class="text-[12px] text-slate-400 dark:text-slate-500 mt-0.5">Kelola pengelompokan material</p>
        </div>
        <button type="button" wire:click="$set('showModal', true)"
        class="flex items-center gap-2 px-3 py-2 bg-[#1a3a6e] hover:bg-[#2251a3]
               text-white text-[12px] font-medium rounded-lg transition-colors">
    <i class="fa-solid fa-plus text-xs"></i>
    Add New Group
</button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-3 gap-4">

        {{-- Total --}}
        <button wire:click="filterByCard('total')" type="button"
                class="text-left bg-white dark:bg-slate-800 rounded-xl border p-4 transition-all
                       {{ $activeCard === 'total'
                           ? 'border-blue-400 ring-2 ring-blue-200 dark:ring-blue-800'
                           : 'border-slate-200 dark:border-slate-700 hover:border-blue-300' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mb-1">Total Group</p>
                    <p class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ $this->stats['total'] }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-ruler-combined text-blue-500 text-lg"></i>
                </div>
            </div>
        </button>

        {{-- Active --}}
        <button wire:click="filterByCard('active')" type="button"
                class="text-left bg-white dark:bg-slate-800 rounded-xl border p-4 transition-all
                       {{ $activeCard === 'active'
                           ? 'border-green-400 ring-2 ring-green-200 dark:ring-green-800'
                           : 'border-slate-200 dark:border-slate-700 hover:border-green-300' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mb-1">Active</p>
                    <p class="text-2xl font-bold text-green-600">{{ $this->stats['active'] }}</p>
                </div>
                <div class="w-10 h-10 bg-green-50 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-circle-check text-green-500 text-lg"></i>
                </div>
            </div>
        </button>

        {{-- Inactive --}}
        <button wire:click="filterByCard('inactive')" type="button"
                class="text-left bg-white dark:bg-slate-800 rounded-xl border p-4 transition-all
                       {{ $activeCard === 'inactive'
                           ? 'border-red-400 ring-2 ring-red-200 dark:ring-red-800'
                           : 'border-slate-200 dark:border-slate-700 hover:border-red-300' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mb-1">Non-Active</p>
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

    {{-- Status filter — custom dropdown --}}
    <div x-data="{ open: false }" class="relative">
        <button type="button" @click="open = !open" @click.outside="open = false"
                class="h-9 px-3 flex items-center gap-2 text-sm border border-slate-200
                       dark:border-slate-600 bg-white dark:bg-slate-700 rounded-lg
                       text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition min-w-32">
            <span class="flex-1 text-left text-xs">
                {{ $statusFilter === '' ? 'All Status' : ($statusFilter === '1' ? 'Active' : 'Non-Active') }}
            </span>
            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform"
               :class="open ? 'rotate-180' : ''"></i>
        </button>
        <div x-show="open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             class="absolute left-0 top-full mt-1 w-36 bg-white dark:bg-slate-800
                    border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-30 p-1">
            @foreach(['' => 'All Status', '1' => 'Active', '0' => 'Non-Active'] as $val => $label)
            <button type="button"
                    wire:click="$set('statusFilter', '{{ $val }}'); open = false"
                    @click="open = false"
                    class="w-full text-left px-3 py-1.5 text-xs rounded-lg transition
                           {{ $statusFilter === (string)$val
                               ? 'bg-[#1a3a6e] text-white'
                               : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    

    {{-- Search --}}
    <div class="relative flex-1 min-w-40">
        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
        <input wire:model.live.debounce.300ms="search"
               type="text" placeholder="Cari kode atau nama..."
               class="w-full pl-8 pr-3 h-9 text-sm border border-slate-200 dark:border-slate-600
                      bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-slate-100
                      rounded-lg outline-none focus:border-[#2251a3] focus:ring-2
                      focus:ring-[#2251a3]/10 transition placeholder-slate-400" />
    </div>

            <div class="flex items-center gap-2 ml-auto">

                {{-- Column toggle --}}
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open" @click.outside="open = false"
                            class="h-9 px-3 flex items-center gap-2 text-sm border border-slate-200
                                   dark:border-slate-600 bg-white dark:bg-slate-700 rounded-lg
                                   text-slate-600 dark:text-slate-300 hover:bg-slate-50
                                   dark:hover:bg-slate-600 transition">
                        <i class="fa-solid fa-table-columns text-xs"></i>
                        <span class="hidden sm:inline text-xs">Column</span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                    </button>
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         class="absolute right-0 top-full mt-1 w-44 bg-white dark:bg-slate-800
                                border border-slate-200 dark:border-slate-700 rounded-xl
                                shadow-xl z-30 p-2">
                        @foreach(['code' => 'Code', 'name' => 'Name', 'note' => 'Note', 'status' => 'Status'] as $key => $label)
                        <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50
                                      dark:hover:bg-slate-700 cursor-pointer text-sm text-slate-600 dark:text-slate-300">
                            <input type="checkbox" wire:model.live="columns.{{ $key }}"
                                   class="accent-[#1a3a6e] w-3.5 h-3.5 rounded" />
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Export --}}
                <button type="button" wire:click="exportData"
                        class="px-3 py-2 flex items-center gap-2 text-xs border border-green-500
                               bg-green-500 hover:bg-green-600 text-white rounded-lg transition">
                    <i class="fa-solid fa-file-arrow-down"></i>
                    <span class="hidden sm:inline">Export</span>
                </button>

            </div>
        </div>

        {{-- Upload panel --}}
        @if($showUpload)
        <div x-data x-show="true"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="px-4 py-3 bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-3 flex-wrap">
                <span class="text-sm text-slate-600 dark:text-slate-300 font-medium">Bulk Upload via Excel:</span>
                <button type="button" wire:click="downloadTemplate"
                        class="flex items-center gap-1.5 text-xs px-3 py-1.5 bg-green-50 dark:bg-green-900/30
                               text-green-700 dark:text-green-400 border border-green-200 dark:border-green-700
                               rounded-lg hover:bg-green-100 transition">
                    <i class="fa-solid fa-file-excel"></i> Download Template
                </button>
                <label class="flex items-center gap-1.5 text-xs px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30
                              text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-700
                              rounded-lg hover:bg-blue-100 transition cursor-pointer">
                    <i class="fa-solid fa-upload"></i> Pilih File
                    <input type="file" accept=".xlsx,.xls" class="hidden" />
                </label>
                <span class="text-xs text-slate-400">Format: .xlsx / .xls</span>
                <button type="button"
                        class="ml-auto flex items-center gap-1.5 text-xs px-3 py-1.5
                               bg-[#1a3a6e] text-white rounded-lg hover:bg-[#2251a3] transition">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Proses Upload
                </button>
            </div>
        </div>
        @endif

        {{-- Bulk action bar --}}
        @if(count($selected) > 0)
        <div class="flex items-center gap-3 px-4 py-2.5 bg-blue-50 dark:bg-blue-900/20
                    border-b border-blue-100 dark:border-blue-800">
            <span class="text-sm font-medium text-blue-700 dark:text-blue-400">
                {{ count($selected) }} item dipilih
            </span>
            <button type="button" wire:click="bulkActivate"
                    class="flex items-center gap-1.5 text-xs px-3 py-1.5 bg-green-500
                           hover:bg-green-600 text-white rounded-lg transition">
                <i class="fa-solid fa-circle-check"></i> Aktifkan
            </button>
            <button type="button" wire:click="bulkDeactivate"
                    class="flex items-center gap-1.5 text-xs px-3 py-1.5 bg-red-500
                           hover:bg-red-600 text-white rounded-lg transition">
                <i class="fa-solid fa-circle-xmark"></i> Nonaktifkan
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
                        <th class="px-3 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wide w-10">No</th>
                        @if($columns['code'])
                        <th class="px-3 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wide">Code</th>
                        @endif
                        @if($columns['name'])
                        <th class="px-3 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wide">Name</th>
                        @endif
                        @if($columns['note'])
                        <th class="px-3 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wide">Note</th>
                        @endif
                        @if($columns['status'])
                        <th class="px-3 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wide">Status</th>
                        @endif
                        <th class="px-3 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wide w-20">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($this->paginatedGroups as $i => $group)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors group">
                        <td class="px-4 py-2.5">
                            <input type="checkbox" wire:model.live="selected" value="{{ $group->id }}"
                                   class="accent-[#1a3a6e] rounded" />
                        </td>
                        <td class="px-3 py-2.5 text-slate-400 text-xs">
                            {{ (($page - 1) * ($perPage ?: $this->allGroups->count())) + $i + 1 }}
                        </td>
                        @if($columns['code'])
                        <td class="px-3 py-2.5">
                            <span class="font-mono text-[12px] font-semibold px-2 py-1 bg-slate-700 text-slate-300
                                          text-slate-700 rounded-md">
                                {{ $group->code }}
                            </span>
                        </td>
                        @endif
                        @if($columns['name'])
                        <td class="px-3 py-2.5 font-medium text-[12px] text-slate-800 dark:text-slate-100">{{ $group->name }}</td>
                        @endif
                        @if($columns['note'])
                        <td class="px-3 py-2.5 text-slate-500 dark:text-slate-400 text-[12px]">
                            {{ $group->note ?: '-' }}
                        </td>
                        @endif
                        @if($columns['status'])
                        <td class="px-3 py-2.5">
                            @if($group->status)
                            <span class="inline-flex items-center gap-1 text-[12px] px-2 py-0.5 rounded-full
                                         bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 font-medium">
                                <i class="fa-solid fa-circle text-[6px]"></i> Active
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 text-[12px] px-2 py-0.5 rounded-full
                                         bg-red-50 dark:bg-red-900/30 text-red-500 dark:text-red-400 font-medium">
                                <i class="fa-solid fa-circle text-[6px]"></i> Non-Active
                            </span>
                            @endif
                        </td>
                        @endif
                        <td class="px-3 py-2.5">
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" wire:click="editGroup({{ $group->id }})"
                                        class="w-7 h-7 flex items-center justify-center rounded-lg
                                               text-slate-400 hover:text-blue-600 hover:bg-blue-50
                                               dark:hover:bg-blue-900/30 transition" title="Edit">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </button>
                                <button type="button"
                                        wire:click="deleteGroup({{ $group->id }})"
                                        wire:confirm="Yakin hapus Group '{{ $group->name }}'?"
                                        class="w-7 h-7 flex items-center justify-center rounded-lg
                                               text-slate-400 hover:text-red-500 hover:bg-red-50
                                               dark:hover:bg-red-900/30 transition" title="Delete">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-14 h-14 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center">
                                    <i class="fa-solid fa-ruler-combined text-slate-300 dark:text-slate-500 text-2xl"></i>
                                </div>
                                <p class="text-slate-400 dark:text-slate-500 text-sm">Tidak ada data ditemukan</p>
                                @if($search || $statusFilter || $categoryFilter)
                                <button type="button"
                                        wire:click="$set('search',''); $set('statusFilter',''); $set('categoryFilter',''); $set('activeCard','')"
                                        class="text-xs text-blue-600 hover:underline">
                                    Reset filter
                                </button>
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
            <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                <span>Show</span>
               {{-- Per Page Dropdown --}}
<div x-data="{ open: false }" class="relative">

    {{-- Trigger --}}
    <button type="button"
            @click="open = !open"
            @click.outside="open = false"
            class="h-8 px-3 flex items-center gap-2 text-sm border border-slate-200
                   dark:border-slate-600 bg-white dark:bg-slate-700 rounded-lg
                   text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition min-w-[70px]">

        <span class="flex-1 text-left text-xs">
            {{ $perPage == 0 ? 'All' : $perPage }}
        </span>

        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform"
           :class="open ? 'rotate-180' : ''"></i>
    </button>

    {{-- Dropdown --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         class="absolute right-0 top-full mt-1 w-24 bg-white dark:bg-slate-800
                border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-30 p-1">

        @foreach([10, 25, 50, 100, 0] as $val)
        <button type="button"
                wire:click="$set('perPage', {{ $val }})"
                @click="open = false"
                class="w-full text-left px-3 py-1.5 text-xs rounded-lg transition
                       {{ $perPage == $val
                           ? 'bg-[#1a3a6e] text-white'
                           : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }}">

            {{ $val == 0 ? 'All' : $val }}

        </button>
        @endforeach

    </div>
</div>
                <span>entries</span>
                <span class="text-slate-400 ml-1">— {{ $this->allGroups->count() }} total</span>
            </div>

            @if($this->totalPages > 1)
            <div class="flex items-center gap-1">
                <button type="button" wire:click="previousPage"
                        @class([
                            'w-8 h-8 flex items-center justify-center rounded-lg border text-xs transition',
                            'border-slate-200 dark:border-slate-600 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700' => $page > 1,
                            'border-slate-100 dark:border-slate-700 text-slate-300 dark:text-slate-600 cursor-not-allowed pointer-events-none' => $page <= 1,
                        ])>
                    <i class="fa-solid fa-chevron-left"></i>
                </button>

                @for($p = 1; $p <= $this->totalPages; $p++)
                <button type="button" wire:click="setPage({{ $p }})"
                        @class([
                            'w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium transition',
                            'bg-[#1a3a6e] text-white' => $page === $p,
                            'border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' => $page !== $p,
                        ])>
                    {{ $p }}
                </button>
                @endfor

                <button type="button" wire:click="nextPage"
                        @class([
                            'w-8 h-8 flex items-center justify-center rounded-lg border text-xs transition',
                            'border-slate-200 dark:border-slate-600 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700' => $page < $this->totalPages,
                            'border-slate-100 dark:border-slate-700 text-slate-300 dark:text-slate-600 cursor-not-allowed pointer-events-none' => $page >= $this->totalPages,
                        ])>
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
            @endif
        </div>
    </div>

    {{-- Modal --}}
   {{-- Modal backdrop --}}
<div x-show="$wire.showModal"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(0,0,0,0.5); display:none;">

    {{-- Modal --}}
<div
    x-data="{ show: @js($showModal) }"
    x-init="$watch('show', val => { if(!val) $wire.call('resetForm') })"
    x-on:livewire-updated.window="show = $wire.showModal"
    x-show="show"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="background:rgba(0,0,0,0.5);">

    <div x-show="show"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.outside="$wire.set('showModal', false)"
         class="w-full max-w-lg bg-white dark:bg-slate-800 rounded-2xl shadow-2xl overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-blue-50 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-ruler-combined text-blue-600 dark:text-blue-400"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">
                        {{ $editId ? 'Edit Group' : 'Add New Group' }}
                    </h3>
                    <p class="text-xs text-slate-400">
                        {{ $editId ? 'Perbarui data satuan' : 'Tambah satuan pengukuran baru' }}
                    </p>
                </div>
            </div>
            <button type="button" wire:click="$set('showModal', false); resetForm"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400
                           hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- Body --}}
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
                        <span class="flex-1 text-left {{ $form_category ? 'text-slate-800 dark:text-slate-100' : 'text-slate-400' }}">
                            {{ $form_category ?: 'Pilih kategori...' }}
                        </span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200"
                           :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-transition
                         class="absolute left-0 top-full mt-1 w-full bg-white dark:bg-slate-800
                                border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-50 p-1">
                        @foreach(['Mass', 'Length', 'Volume', 'Quantity'] as $cat)
                        <button type="button"
                                wire:click="$set('form_category', '{{ $cat }}')"
                                @click="open = false"
                                class="w-full text-left px-3 py-2 text-sm rounded-lg transition
                                       {{ $form_category === $cat
                                           ? 'bg-[#1a3a6e] text-white'
                                           : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' }}">
                            {{ $cat }}
                        </button>
                        @endforeach
                    </div>
                </div>
                @error('form_category')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
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
            <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                <div>
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Status</p>
                    <p class="text-xs text-slate-400">{{ $form_status ? 'Group aktif' : 'Group tidak aktif' }}</p>
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

        {{-- Footer --}}
        <div class="flex items-center gap-3 px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50">
            <button type="button" wire:click="$set('showModal', false); resetForm"
                    class="flex-1 h-10 text-sm font-medium border border-slate-200 dark:border-slate-600
                           text-slate-600 dark:text-slate-300 rounded-lg hover:bg-slate-100 transition">
                Batal
            </button>
            <button type="button" wire:click="saveGroup"
                    wire:loading.attr="disabled" wire:target="saveGroup"
                    class="flex-1 h-10 flex items-center justify-center gap-2 text-sm font-medium
                           bg-[#1a3a6e] hover:bg-[#2251a3] text-white rounded-lg transition
                           disabled:opacity-70 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="saveGroup" class="flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    {{ $editId ? 'Update Group' : 'Simpan Group' }}
                </span>
                <span wire:loading wire:target="saveGroup" class="flex items-center gap-2">
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