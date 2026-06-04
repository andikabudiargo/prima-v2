<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware('auth')->group(function () {
    Volt::route('dashboard', 'pages.dashboard')->name('dashboard');

    // Material
    Volt::route('material/material', 'pages.material.material')->name('material.material');
    Volt::route('material/registration', 'pages.material.registration')->name('material.registration');
    Volt::route('material/category', 'pages.material.category')->name('material.category');
    Volt::route('material/group', 'pages.material.group')->name('material.group');
    Volt::route('material/uom', 'pages.material.uom')->name('material.uom');
    Volt::route('material/uom-conversion', 'pages.material.uom-conversion')->name('material.uom-conversion');

    // Sales
    Volt::route('sales/customer', 'pages.sales.customer')->name('sales.customer');
    Volt::route('sales/order', 'pages.sales.order')->name('sales.order');
    Volt::route('sales/delivery-order', 'pages.sales.delivery-order')->name('sales.delivery-order');
    Volt::route('sales/quotation', 'pages.sales.quotation')->name('sales.quotation');
    Volt::route('sales/forecasting', 'pages.sales.forecasting')->name('sales.forecasting');
    Volt::route('sales/delivery-term', 'pages.sales.delivery-term')->name('sales.delivery-term');
    Volt::route('sales/return-order', 'pages.sales.return-order')->name('sales.return-order');
    Volt::route('sales/replacement-order', 'pages.sales.replacement-order')->name('sales.replacement-order');

    // Procurement
    Volt::route('procurement/supplier', 'pages.procurement.supplier')->name('procurement.supplier');
    Volt::route('procurement/purchase-request', 'pages.procurement.purchase-request')->name('procurement.purchase-request');
    Volt::route('procurement/purchase-order', 'pages.procurement.purchase-order')->name('procurement.purchase-order');
    Volt::route('procurement/receipt-order', 'pages.procurement.receipt-order')->name('procurement.receipt-order');
    Volt::route('procurement/rfq', 'pages.procurement.rfq')->name('procurement.rfq');
    Volt::route('procurement/return-order', 'pages.procurement.return-order')->name('procurement.return-order');
    Volt::route('procurement/replacement-order', 'pages.procurement.replacement-order')->name('procurement.replacement-order');

    // Inventory
    Volt::route('inventory/stocks', 'pages.inventory.stocks')->name('inventory.stocks');
    Volt::route('inventory/movements', 'pages.inventory.movements')->name('inventory.movements');
    Volt::route('inventory/transfers', 'pages.inventory.transfers')->name('inventory.transfers');
    Volt::route('inventory/adjustments', 'pages.inventory.adjustments')->name('inventory.adjustments');
    Volt::route('inventory/stock-taking', 'pages.inventory.stock-taking')->name('inventory.stock-taking');
    Volt::route('inventory/mrp', 'pages.inventory.mrp')->name('inventory.mrp');

    // Manufacturing
    Volt::route('manufacturing/bom', 'pages.manufacturing.bom')->name('manufacturing.bom');
    Volt::route('manufacturing/production-planning', 'pages.manufacturing.production-planning')->name('manufacturing.production-planning');
    Volt::route('manufacturing/production-order', 'pages.manufacturing.production-order')->name('manufacturing.production-order');
    Volt::route('manufacturing/production-control', 'pages.manufacturing.production-control')->name('manufacturing.production-control');
    Volt::route('manufacturing/workstation', 'pages.manufacturing.workstation')->name('manufacturing.workstation');
    Volt::route('manufacturing/routing-line', 'pages.manufacturing.routing-line')->name('manufacturing.routing-line');

    // Accounting
    Volt::route('accounting/journal', 'pages.accounting.journal')->name('accounting.journal');
    Volt::route('accounting/balance-sheet', 'pages.accounting.balance-sheet')->name('accounting.balance-sheet');
    Volt::route('accounting/income-statement', 'pages.accounting.income-statement')->name('accounting.income-statement');
    Volt::route('accounting/financial-report', 'pages.accounting.financial-report')->name('accounting.financial-report');

    // System
    Volt::route('system/users', 'pages.system.users')->name('system.users');
    Volt::route('system/settings', 'pages.system.settings')->name('system.settings');
});

require __DIR__.'/auth.php';
