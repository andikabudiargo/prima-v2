<aside
    x-data="sidebarComp()"
    :class="isExpanded ? 'w-64' : 'w-16'"
    @mouseenter="hovered = true"
    @mouseleave="hovered = false"
    class="relative flex-shrink-0 bg-[#224b8f] flex flex-col
       transition-[width] duration-300 ease-in-out overflow-hidden
       hidden lg:flex">

    {{-- Brand --}}
    <div class="flex items-center h-16 border-b border-white/10 flex-shrink-0 px-4">
        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center flex-shrink-0 p-1">
    <img src="{{ asset('ui/prima.png') }}"
         alt="Prima Logo"
         class="w-full h-full object-contain" />
</div>
        <div class="ml-3 overflow-hidden whitespace-nowrap transition-all duration-300"
             :class="isExpanded ? 'opacity-100 w-40' : 'opacity-0 w-0'">
            <div class="text-white text-[15px] font-semibold leading-tight">Prima Software</div>
            <div class="text-[#a8c4f0] text-[8px] tracking-widest uppercase">ERP Standard Plan</div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto overflow-x-hidden py-2 scrollbar-hide"
         :class="isExpanded ? 'px-6' : 'px-4'">

        {{-- Section: Menu --}}
        <div class="text-[10px] text-[#a8c4f0]/60 tracking-widest uppercase px-2 pt-3 pb-1 whitespace-nowrap transition-opacity duration-200"
             :class="isExpanded ? 'opacity-100' : 'opacity-0'">Menu</div>

        <a href="{{ route('dashboard') }}" wire:navigate
           class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">
            <i class="fa-solid fa-gauge nav-icon"></i>
            <span class="nav-label" :class="isExpanded ? 'opacity-100' : 'opacity-0 w-0'">Dashboard</span>
        </a>

        {{-- Material --}}
        <div x-data="{ open: {{ request()->routeIs('material.*') ? 'true' : 'false' }} }">
            <button @click="if(isExpanded) open = !open"
                    class="nav-link w-full text-left {{ request()->routeIs('material.*') ? 'nav-link-active' : '' }}">
                <i class="fa-solid fa-boxes-stacked nav-icon"></i>
                <span class="nav-label" :class="isExpanded ? 'opacity-100' : 'opacity-0 w-0'">Material</span>
                <i class="fa-solid fa-chevron-down text-[9px] ml-auto transition-all duration-200 flex-shrink-0"
                   :class="[open ? 'rotate-180' : '', isExpanded ? 'opacity-100' : 'opacity-0 w-0']"></i>
            </button>
            <div x-show="open && isExpanded" x-collapse>
                <a href="#" class="submenu-link {{ request()->routeIs('material.material') ? 'submenu-link-active' : '' }}">Material</a>
                <a href="{{ route('material.registration') }}" wire:navigate class="submenu-link {{ request()->routeIs('material.registration') ? 'submenu-link-active' : '' }}">Registration</a>
                <a href="#" class="submenu-link">Category</a>
                <a href="{{ route('material.group') }}" wire:navigate class="submenu-link {{ request()->routeIs('material.group') ? 'submenu-link-active' : '' }}">Group</a>
                <a href="{{ route('material.uom') }}" wire:navigate
                    class="submenu-link {{ request()->routeIs('material.uom') ? 'submenu-link-active' : '' }}">
                        Unit of Measurement (UoM)
                </a>
                
            </div>
        </div>

        {{-- Sales --}}
        <div x-data="{ open: {{ request()->routeIs('sales.*') ? 'true' : 'false' }} }">
            <button @click="if(isExpanded) open = !open" class="nav-link w-full text-left {{ request()->routeIs('sales.*') ? 'nav-link-active' : '' }}">
                <i class="fa-solid fa-chart-line nav-icon"></i>
                <span class="nav-label" :class="isExpanded ? 'opacity-100' : 'opacity-0 w-0'">Sales</span>
                <i class="fa-solid fa-chevron-down text-[9px] ml-auto transition-all duration-200 flex-shrink-0"
                   :class="[open ? 'rotate-180' : '', isExpanded ? 'opacity-100' : 'opacity-0 w-0']"></i>
            </button>
            <div x-show="open && isExpanded" x-collapse>
                <a href="#" class="submenu-link">Customer</a>
                <a href="#" class="submenu-link">Sales Order</a>
                <a href="#" class="submenu-link">Delivery Order</a>
                <a href="#" class="submenu-link">Quotation</a>
                <a href="#" class="submenu-link">Forecasting</a>
                <a href="#" class="submenu-link">Delivery Term</a>
                <a href="#" class="submenu-link">Return Order</a>
                <a href="#" class="submenu-link">Replacement Order</a>
            </div>
        </div>

        {{-- Procurement --}}
        <div x-data="{ open: {{ request()->routeIs('procurement.*') ? 'true' : 'false' }} }">
            <button @click="if(isExpanded) open = !open" class="nav-link w-full text-left {{ request()->routeIs('procurement.*') ? 'nav-link-active' : '' }}">
                <i class="fa-solid fa-cart-flatbed nav-icon"></i>
                <span class="nav-label" :class="isExpanded ? 'opacity-100' : 'opacity-0 w-0'">Procurement</span>
                <i class="fa-solid fa-chevron-down text-[9px] ml-auto transition-all duration-200 flex-shrink-0"
                   :class="[open ? 'rotate-180' : '', isExpanded ? 'opacity-100' : 'opacity-0 w-0']"></i>
            </button>
            <div x-show="open && isExpanded" x-collapse>
                <a href="#" class="submenu-link">Supplier</a>
                <a href="#" class="submenu-link">Purchase Request</a>
                <a href="#" class="submenu-link">Purchase Order</a>
                <a href="#" class="submenu-link">Receipt Order</a>
                <a href="#" class="submenu-link">Request for Quotation (RFQ)</a>
                <a href="#" class="submenu-link">Return Order</a>
                <a href="#" class="submenu-link">Replacement Order</a>
            </div>
        </div>

        {{-- Inventory --}}
        <div x-data="{ open: {{ request()->routeIs('inventory.*') ? 'true' : 'false' }} }">
            <button @click="if(isExpanded) open = !open" class="nav-link w-full text-left {{ request()->routeIs('inventory.*') ? 'nav-link-active' : '' }}">
                <i class="fa-solid fa-box-open nav-icon"></i>
                <span class="nav-label" :class="isExpanded ? 'opacity-100' : 'opacity-0 w-0'">Inventory</span>
                <i class="fa-solid fa-chevron-down text-[9px] ml-auto transition-all duration-200 flex-shrink-0"
                   :class="[open ? 'rotate-180' : '', isExpanded ? 'opacity-100' : 'opacity-0 w-0']"></i>
            </button>
            <div x-show="open && isExpanded" x-collapse>
                <a href="#" class="submenu-link">Stocks</a>
                <a href="#" class="submenu-link">Movements</a>
                <a href="#" class="submenu-link">Transfers</a>
                <a href="#" class="submenu-link">Adjustments</a>
                <a href="#" class="submenu-link">Stock Taking</a>
                <a href="#" class="submenu-link">Material Requirement Planning</a>
            </div>
        </div>

        {{-- Warehouse --}}
        <div x-data="{ open: {{ request()->routeIs('warehouse.*') ? 'true' : 'false' }} }">
            <button @click="if(isExpanded) open = !open" class="nav-link w-full text-left {{ request()->routeIs('inventory.*') ? 'nav-link-active' : '' }}">
                <i class="fa-solid fa-warehouse nav-icon"></i>
                <span class="nav-label" :class="isExpanded ? 'opacity-100' : 'opacity-0 w-0'">Warehouse</span>
                <i class="fa-solid fa-chevron-down text-[9px] ml-auto transition-all duration-200 flex-shrink-0"
                   :class="[open ? 'rotate-180' : '', isExpanded ? 'opacity-100' : 'opacity-0 w-0']"></i>
            </button>
            <div x-show="open && isExpanded" x-collapse>
                <a href="#" class="submenu-link">Factory</a>
                <a href="#" class="submenu-link">Location</a>
                <a href="#" class="submenu-link">Area</a>
                <a href="#" class="submenu-link">Shelves</a>
                <a href="#" class="submenu-link">Putaway</a>
                <a href="#" class="submenu-link">Picking</a>
            </div>
        </div>

        {{-- Manufacturing --}}
        <div x-data="{ open: {{ request()->routeIs('manufacturing.*') ? 'true' : 'false' }} }">
            <button @click="if(isExpanded) open = !open" class="nav-link w-full text-left {{ request()->routeIs('manufacturing.*') ? 'nav-link-active' : '' }}">
                <i class="fa-solid fa-gears nav-icon"></i>
                <span class="nav-label" :class="isExpanded ? 'opacity-100' : 'opacity-0 w-0'">Manufacturing</span>
                <i class="fa-solid fa-chevron-down text-[9px] ml-auto transition-all duration-200 flex-shrink-0"
                   :class="[open ? 'rotate-180' : '', isExpanded ? 'opacity-100' : 'opacity-0 w-0']"></i>
            </button>
            <div x-show="open && isExpanded" x-collapse>
                <a href="#" class="submenu-link">Bill of Materials (BoM)</a>
                <a href="#" class="submenu-link">Production Planning</a>
                <a href="#" class="submenu-link">Production Order</a>
                <a href="#" class="submenu-link">Production Control</a>
                <a href="#" class="submenu-link">Workstation</a>
                <a href="#" class="submenu-link">Routing Line</a>
            </div>
        </div>

         {{-- Quality --}}
        <div x-data="{ open: {{ request()->routeIs('quality.*') ? 'true' : 'false' }} }">
            <button @click="if(isExpanded) open = !open" class="nav-link w-full text-left {{ request()->routeIs('quality.*') ? 'nav-link-active' : '' }}">
                <i class="fa-solid fa-clipboard-check nav-icon"></i>
                <span class="nav-label" :class="isExpanded ? 'opacity-100' : 'opacity-0 w-0'">Quality</span>
                <i class="fa-solid fa-chevron-down text-[9px] ml-auto transition-all duration-200 flex-shrink-0"
                   :class="[open ? 'rotate-180' : '', isExpanded ? 'opacity-100' : 'opacity-0 w-0']"></i>
            </button>
            <div x-show="open && isExpanded" x-collapse>
                <a href="#" class="submenu-link">Inspections</a>
                <a href="#" class="submenu-link">Defects</a>
            </div>
        </div>

        {{-- Section: Keuangan --}}
        <div class="text-[10px] text-[#a8c4f0]/60 tracking-widest uppercase px-2 pt-4 pb-1 whitespace-nowrap transition-opacity duration-200"
             :class="isExpanded ? 'opacity-100' : 'opacity-0'">Finance & Acc</div>

        <div x-data="{ open: {{ request()->routeIs('finance.*') ? 'true' : 'false' }} }">
            <button @click="if(isExpanded) open = !open" class="nav-link w-full text-left {{ request()->routeIs('finance.*') ? 'nav-link-active' : '' }}">
                <i class="fa-solid fa-file-invoice-dollar nav-icon"></i>
                <span class="nav-label" :class="isExpanded ? 'opacity-100' : 'opacity-0 w-0'">Finance</span>
                <i class="fa-solid fa-chevron-down text-[9px] ml-auto transition-all duration-200 flex-shrink-0"
                   :class="[open ? 'rotate-180' : '', isExpanded ? 'opacity-100' : 'opacity-0 w-0']"></i>
            </button>
            <div x-show="open && isExpanded" x-collapse>
                <a href="#" class="submenu-link">Invoice Supplier (AP)</a>
                <a href="#" class="submenu-link">Invoice Customer (AR)</a>
                <a href="#" class="submenu-link">Cash & Bank</a>
                <a href="#" class="submenu-link">Offset Letter</a>
                <a href="#" class="submenu-link">Debit Note</a>
                <a href="#" class="submenu-link">Credit Note</a>
                <a href="#" class="submenu-link">Reimbursement</a>
            </div>
        </div>

          <div x-data="{ open: {{ request()->routeIs('accounting.*') ? 'true' : 'false' }} }">
            <button @click="if(isExpanded) open = !open" class="nav-link w-full text-left {{ request()->routeIs('accounting.*') ? 'nav-link-active' : '' }}">
                <i class="fa-solid fa-coins nav-icon"></i>
                <span class="nav-label" :class="isExpanded ? 'opacity-100' : 'opacity-0 w-0'">Accounting</span>
                <i class="fa-solid fa-chevron-down text-[9px] ml-auto transition-all duration-200 flex-shrink-0"
                   :class="[open ? 'rotate-180' : '', isExpanded ? 'opacity-100' : 'opacity-0 w-0']"></i>
            </button>
            <div x-show="open && isExpanded" x-collapse>
                <a href="#" class="submenu-link">Chart of Account (CoA)</a>
                <a href="#" class="submenu-link">General Journal</a>
                <a href="#" class="submenu-link">Cost Center</a>
                <a href="#" class="submenu-link">Profit Center</a>
                <a href="#" class="submenu-link">Product Costing (HPP)</a>
                <a href="#" class="submenu-link">Budget Control</a>
                <a href="#" class="submenu-link">Assets</a>
            </div>
        </div>

          <div x-data="{ open: {{ request()->routeIs('statement.*') ? 'true' : 'false' }} }">
            <button @click="if(isExpanded) open = !open" class="nav-link w-full text-left {{ request()->routeIs('statement.*') ? 'nav-link-active' : '' }}">
                <i class="fa-solid fa-scale-balanced nav-icon"></i>
                <span class="nav-label" :class="isExpanded ? 'opacity-100' : 'opacity-0 w-0'">Statement</span>
                <i class="fa-solid fa-chevron-down text-[9px] ml-auto transition-all duration-200 flex-shrink-0"
                   :class="[open ? 'rotate-180' : '', isExpanded ? 'opacity-100' : 'opacity-0 w-0']"></i>
            </button>
            <div x-show="open && isExpanded" x-collapse>
                <a href="#" class="submenu-link">General Ledger</a>
                <a href="#" class="submenu-link">Cash Flow</a>
                <a href="#" class="submenu-link">Trial Balance</a>
                <a href="#" class="submenu-link">Laba Rugi</a>
                <a href="#" class="submenu-link">Neraca</a>
                <a href="#" class="submenu-link">Reconciliation</a>
            </div>
        </div>

        <div x-data="{ open: {{ request()->routeIs('finance.*') ? 'true' : 'false' }} }">
            <button @click="if(isExpanded) open = !open" class="nav-link w-full text-left {{ request()->routeIs('finance.*') ? 'nav-link-active' : '' }}">
                <i class="fa-solid fa-lock nav-icon"></i>
                <span class="nav-label" :class="isExpanded ? 'opacity-100' : 'opacity-0 w-0'">System Control</span>
                <i class="fa-solid fa-chevron-down text-[9px] ml-auto transition-all duration-200 flex-shrink-0"
                   :class="[open ? 'rotate-180' : '', isExpanded ? 'opacity-100' : 'opacity-0 w-0']"></i>
            </button>
            <div x-show="open && isExpanded" x-collapse>
                <a href="#" class="submenu-link">Lock Transactions</a>
                <a href="#" class="submenu-link">Tax Management</a>
                <a href="#" class="submenu-link">Currency</a>
                <a href="#" class="submenu-link">Payment Term</a>
            </div>
        </div>


        {{-- Section: System --}}
        <div class="text-[10px] text-[#a8c4f0]/60 tracking-widest uppercase px-2 pt-4 pb-1 whitespace-nowrap transition-opacity duration-200"
             :class="isExpanded ? 'opacity-100' : 'opacity-0'">Settings</div>

        <a href="#" class="nav-link">
            <i class="fa-solid fa-users nav-icon"></i>
            <span class="nav-label" :class="isExpanded ? 'opacity-100' : 'opacity-0 w-0'">Users</span>
        </a>
         <a href="#" class="nav-link">
            <i class="fa-solid fa-id-card nav-icon"></i>
            <span class="nav-label" :class="isExpanded ? 'opacity-100' : 'opacity-0 w-0'">Roles</span>
        </a>
         <a href="#" class="nav-link">
            <i class="fa-solid fa-key nav-icon"></i>
            <span class="nav-label" :class="isExpanded ? 'opacity-100' : 'opacity-0 w-0'">Permissions</span>
        </a>
         <a href="#" class="nav-link">
            <i class="fa-solid fa-file-signature nav-icon"></i>
            <span class="nav-label" :class="isExpanded ? 'opacity-100' : 'opacity-0 w-0'">Approvals</span>
        </a>
        <a href="#" class="nav-link">
            <i class="fa-solid fa-sliders nav-icon"></i>
            <span class="nav-label" :class="isExpanded ? 'opacity-100' : 'opacity-0 w-0'">System Configuration</span>
        </a>
         <a href="#" class="nav-link">
            <i class="fa-solid fa-circle-info nav-icon"></i>
            <span class="nav-label" :class="isExpanded ? 'opacity-100' : 'opacity-0 w-0'">Log Activity</span>
        </a>
    </nav>

    {{-- Logout --}}
    <div class="border-t border-white/10 px-4 flex-shrink-0">
        <button wire:click="logout" class="nav-link w-full text-left opacity-50 hover:opacity-80 hover">
            <i class="fa-solid fa-right-from-bracket nav-icon"></i>
            <span class="nav-label" :class="isExpanded ? 'opacity-100' : 'opacity-0 w-0'">Logout</span>
        </button>
    </div>

</aside>