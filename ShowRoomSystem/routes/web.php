<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/logout', function () {
    session()->flush();
    return redirect('/login');
});

/*
|--------------------------------------------------------------------------
| Protected Routes (require auth.session)
|--------------------------------------------------------------------------
*/

Route::middleware('auth.session')->group(function () {

    // Dashboard – shows your Blade view
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Customer CRUD
    Route::resource('customers', CustomerController::class);
});