<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Web — SEN SOLUTION ELECTRONIQUE SI
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('login'));

Route::middleware(['auth', 'active'])->group(function () {

    // ── Dashboard (tous les rôles authentifiés) ─────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Profil ────────────────────────────────────────────────
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Produits & Catégories (Admin, Gestionnaire) ───────────
    Route::middleware('role:admin,manager')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('products', ProductController::class);
        Route::resource('suppliers', SupplierController::class)->except(['show']);
    });

    // ── Clients, Ventes, Factures (Admin, Gestionnaire, Caissier)
    Route::middleware('role:admin,manager,cashier')->group(function () {
        Route::resource('customers', CustomerController::class)->except(['show']);
        Route::get('sales/exchange-products/search', [SaleController::class, 'searchExchangeProducts'])->name('sales.exchange-products.search');
        Route::post('sales/exchange-products/store', [SaleController::class, 'storeExchangeProduct'])->name('sales.exchange-products.store');
        Route::resource('sales', SaleController::class)->except(['show']);
        Route::resource('invoices', InvoiceController::class)->except(['show']);
        Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
        Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
        Route::get('invoices/{invoice}/whatsapp', [InvoiceController::class, 'sendWhatsApp'])->name('invoices.whatsapp');
    });

    // ── Utilisateurs (Admin et Gestionnaire) ─────────────────
    Route::middleware('role:admin,manager')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });
});

require __DIR__.'/auth.php';
