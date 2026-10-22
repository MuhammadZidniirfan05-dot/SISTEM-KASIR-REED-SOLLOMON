<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;

// ==========================================
// RUTE UNTUK TAMU (BELUM LOGIN)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.process');
});

// ==========================================
// RUTE UTAMA (SUDAH LOGIN)
// ==========================================
Route::middleware('auth')->group(function () {

    // ------------------------------------------
    // RUTE UMUM (ADMIN & KASIR)
    // ------------------------------------------
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/about', function () {
        return view('about.index');
    })->name('about');

    Route::get('/products/search', [TransactionController::class, 'searchProduct'])
        ->name('products.search');

    // ==========================================
    // CUSTOMER (ADMIN & KASIR)
    // ==========================================
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');


    // ==========================================
    // KHUSUS ADMIN
    // ==========================================
    Route::middleware('checkrole:admin')->group(function () {

        // Categories
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // Products
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::post('/rs-encode', [ProductController::class, 'calculateRS'])->name('rs.encode');

        Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
        Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

        // Reports
        Route::get('/report', [ReportController::class, 'index'])->name('report.index');
        Route::get('/report/filter', [ReportController::class, 'filter'])->name('report.filter');
        Route::get('/report/print/{start_date}/{end_date}', [ReportController::class, 'print'])->name('report.print');
    });


    // ==========================================
    // KHUSUS KASIR
    // ==========================================
    Route::middleware('checkrole:kasir')->group(function () {

        // Transactions
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');

        Route::post('/rs-decode', [TransactionController::class, 'decodeRS'])->name('rs.decode');

        Route::get('/transactions/{id}/print', [TransactionController::class, 'print'])->name('transactions.print');
        Route::get('/transactions/{id}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::get('/transactions/{id}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
        Route::put('/transactions/{id}', [TransactionController::class, 'update'])->name('transactions.update');
        Route::delete('/transactions/{id}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    });

});