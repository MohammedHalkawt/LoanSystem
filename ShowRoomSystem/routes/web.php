<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {

    if (!session()->has('user_id')) {
        return redirect('/login');
    }

    return "Welcome " . session('user_name') .
           " | Role: " . session('user_role') .
           " | <a href='/logout'>Logout</a>";

});

Route::get('/logout', function () {

    if (!session()->has('user_id')) {
        return redirect('/login');
    }

    session()->flush();
    return redirect('/login');

});
