<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;

Route::get('/', function () {
    return view('welcome');
});

// Registration routes
Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');

// Login routes
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Role-based dashboard routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard/super-admin', function () {
        return view('dashboards.super_admin');
    })->name('dashboard.super_admin');

    Route::get('/dashboard/admin', function () {
        return view('dashboards.admin');
    })->name('dashboard.admin');

    Route::get('/dashboard/teacher', function () {
        return view('dashboards.teacher');
    })->name('dashboard.teacher');

    Route::get('/dashboard/student', function () {
        return view('dashboards.student');
    })->name('dashboard.student');
});
