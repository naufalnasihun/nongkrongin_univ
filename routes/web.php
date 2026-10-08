<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ===============================
// LOGIN
// ===============================

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.process');


// ===============================
// LOGOUT
// ===============================

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');


// ===============================
// HALAMAN YANG MEMBUTUHKAN LOGIN
// ===============================

Route::middleware('auth')->group(function () {

    // ===============================
    // DASHBOARD ADMIN
    // ===============================

    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');


    // ===============================
    // MANAGEMENT USER
    // ===============================

    Route::resource('/admin/users', UserController::class)
        ->except(['show'])
        ->names('admin.users');


    // ===============================
    // DASHBOARD KASIR
    // ===============================

    Route::get('/kasir/dashboard', function () {
        return view('kasir.dashboard');
    })->name('kasir.dashboard');


    // ===============================
    // DASHBOARD CUSTOMER
    // ===============================

    Route::get('/customer/dashboard', function () {
        return view('customer.dashboard');
    })->name('customer.dashboard');

});