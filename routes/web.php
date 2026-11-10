<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\DashboardController;     // 1. Import DashboardController
use App\Http\Controllers\Admin\AdminOrderController;     // 1. Import AdminOrderController
use App\Http\Controllers\ProductCatalogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerOrderController;

// ==========================================
// M4.2: ROUTE PUBLIK (Bisa diakses umum)
// ==========================================
Route::get('/', [ProductCatalogController::class, 'index'])->name('shop.index');
Route::get('/produk/{product:slug}', [ProductCatalogController::class, 'show'])->name('shop.show');


// ==========================================
// ROUTE AUTH (Guest)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');


// ==========================================
// M5.3 & M6.7: ROUTE CUSTOMER (Cart & Checkout)
// ==========================================
Route::middleware(['auth', 'role:customer'])->group(function () {
    // Cart Routes (M5.3)
    Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
    Route::post('/keranjang/{product}', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/keranjang/{product}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/keranjang/{product}', [CartController::class, 'destroy'])->name('cart.destroy');

    // Checkout & Order Routes (M6.7)
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/pesanan', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
});


// ==========================================
// M7.4 & ADMIN ROUTES (Khusus Admin)
// ==========================================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    // Dashboard Admin (M7.4)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Master Kategori & Produk (Milestone 2)
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::resource('products', ProductController::class)->except('show');

    // Manajemen Transaksi / Pesanan Admin (M7.4)
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/confirm', [AdminOrderController::class, 'confirm'])->name('orders.confirm');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::patch('/orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');
});