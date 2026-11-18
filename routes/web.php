<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\LecturerAccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/account', [AccountController::class, 'show'])->name('account.show');
    Route::get('/account/password', [AccountController::class, 'editPassword'])->name('account.password.edit');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');

    Route::middleware('role:mahasiswa')
        ->get('/mahasiswa/dashboard', [DashboardController::class, 'show'])
        ->name('mahasiswa.dashboard');

    Route::middleware('role:guru_dosen')
        ->get('/guru-dosen/dashboard', [DashboardController::class, 'show'])
        ->name('lecturer.dashboard');

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', [DashboardController::class, 'show'])->name('admin.dashboard');

        Route::get('/admin/lecturers', [LecturerAccountController::class, 'index'])->name('admin.lecturers.index');
        Route::post('/admin/lecturers', [LecturerAccountController::class, 'store'])->name('admin.lecturers.store');
        Route::post('/admin/lecturers/{lecturer}/temporary-password', [LecturerAccountController::class, 'resetTemporaryPassword'])
            ->name('admin.lecturers.temporary-password');
    });
});
