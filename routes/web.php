<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GudangController;
use App\Http\Controllers\KasirController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Retail Market Web Application
|--------------------------------------------------------------------------
*/

// Halaman Utama & Autentikasi
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 1. Modul UKasir (Desktop Browser POS)
Route::middleware(['auth', 'role:kasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/', [KasirController::class, 'pos'])->name('pos');
    Route::get('/receipt/{invoice_code}', [KasirController::class, 'receipt'])->name('receipt');
});

// 2. Modul UGudang (Warehouse & Stock Management)
Route::middleware(['auth', 'role:gudang'])->prefix('gudang')->name('gudang.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('gudang.products');
    });
    Route::get('/products', [GudangController::class, 'products'])->name('products');
    Route::get('/stock-in', [GudangController::class, 'stockIn'])->name('stock-in');
    Route::get('/adjustment', [GudangController::class, 'adjustment'])->name('adjustment');
    Route::get('/mutations', [GudangController::class, 'mutations'])->name('mutations');
});

// 3. Modul UAdmin (Mobile-First Analytical Dashboard - Read-Only)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/transactions/{id}', [AdminController::class, 'transactionDetail'])->name('transactions.detail');
});
