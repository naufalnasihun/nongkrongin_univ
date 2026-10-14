<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KantinController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\KasirPollingController;
use App\Http\Controllers\KasirRekapController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TopupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarungController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// ===============================
// LOGIN & LOGOUT
// ===============================

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login.process');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');

// ===============================
// HALAMAN YANG MEMBUTUHKAN LOGIN
// ===============================

Route::middleware('auth')->group(function () {

    // ===============================
    // PROFIL (semua role)
    // ===============================

    Route::get('/profil', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profil', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::patch('/profil/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password');

    // ===============================
    // ROUTE ADMIN
    // ===============================

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard', [AdminController::class, 'dashboard'])
            ->name('dashboard');

        Route::resource('/users', UserController::class)
            ->except(['show']);

        Route::patch('/menus/bulk-stok', [MenuController::class, 'bulkStok'])
            ->name('menus.bulk-stok');

        Route::resource('/menus', MenuController::class)
            ->except(['show']);

        Route::resource('/kantins', KantinController::class)
            ->except(['show']);

        Route::resource('/warungs', WarungController::class)
            ->except(['show']);

        Route::get('/orders', [PesananController::class, 'adminIndex'])
            ->name('orders.index');

        Route::get('/laporan', [LaporanController::class, 'index'])
            ->name('laporan.index');

        Route::get('/laporan/export/excel', [LaporanController::class, 'exportExcel'])
            ->name('laporan.excel');

        Route::get('/laporan/export/csv', [LaporanController::class, 'exportCsv'])
            ->name('laporan.csv');

        Route::get('/laporan/cetak', [LaporanController::class, 'cetak'])
            ->name('laporan.cetak');
    });

    // ===============================
    // ROUTE KASIR
    // ===============================

    Route::middleware('role:kasir')->prefix('kasir')->name('kasir.')->group(function () {

        Route::get('/dashboard', [KasirController::class, 'dashboard'])
            ->name('dashboard');

        Route::get('/orders', [KasirController::class, 'orders'])
            ->name('orders.index');

        Route::patch('/orders/{pesanan}/status', [KasirController::class, 'updateStatus'])
            ->name('orders.status');

        Route::patch('/orders/{pesanan}/paid', [KasirController::class, 'markPaid'])
            ->name('orders.paid');

        Route::get('/orders/{pesanan}', [KasirController::class, 'show'])
            ->name('orders.show');

        Route::get('/orders/{pesanan}/struk', [KasirController::class, 'struk'])
            ->name('orders.struk');

        Route::get('/orders/{pesanan}/struk/pdf', [KasirController::class, 'strukPdf'])
            ->name('orders.struk.pdf');

        // Polling notifikasi realtime (dipanggil JS berkala).
        Route::get('/polling', KasirPollingController::class)
            ->name('polling');

        // Rekap harian & tutup buku.
        Route::get('/rekap', [KasirRekapController::class, 'index'])
            ->name('rekap');

        // Konfirmasi top-up saldo customer.
        Route::get('/topup', [TopupController::class, 'kasirIndex'])
            ->name('topup.index');
        Route::patch('/topup/{topup}/setujui', [TopupController::class, 'setujui'])
            ->name('topup.setujui');
        Route::patch('/topup/{topup}/tolak', [TopupController::class, 'tolak'])
            ->name('topup.tolak');
    });

    // ===============================
    // ROUTE CUSTOMER
    // ===============================

    Route::middleware('role:customer')->prefix('customer')->name('customer.')->group(function () {

        Route::get('/dashboard', [PesananController::class, 'customerDashboard'])
            ->name('dashboard');

        // Saldo & top-up customer.
        Route::get('/saldo', [TopupController::class, 'index'])
            ->name('saldo.index');
        Route::post('/saldo/topup', [TopupController::class, 'store'])
            ->name('saldo.topup');

        Route::get('/orders', [PesananController::class, 'customerOrders'])
            ->name('orders.index');

        Route::get('/orders/{pesanan}/struk', [PesananController::class, 'struk'])
            ->name('orders.struk');

        Route::get('/orders/{pesanan}/struk/pdf', [PesananController::class, 'strukPdf'])
            ->name('orders.struk.pdf');

        Route::post('/cart/add/{menu}', [KeranjangController::class, 'add'])
            ->name('cart.add');

        Route::patch('/cart/item/{item}', [KeranjangController::class, 'update'])
            ->name('cart.update');

        Route::delete('/cart/item/{item}', [KeranjangController::class, 'remove'])
            ->name('cart.remove');

        Route::post('/checkout', [KeranjangController::class, 'checkout'])
            ->name('checkout');
    });
});
