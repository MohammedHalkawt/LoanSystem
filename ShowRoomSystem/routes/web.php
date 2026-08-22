<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RentController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', fn() => redirect('/login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/logout', function () { 
    session()->flush();
    return redirect('/login');
})->name('logout');

// Protected routes (require auth.session)
Route::middleware('auth.session')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('customers', CustomerController::class);
    Route::resource('purchases', PurchaseController::class);
    Route::resource('rents', RentController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
});
