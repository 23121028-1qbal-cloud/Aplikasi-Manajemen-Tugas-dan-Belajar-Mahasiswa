<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\EnrollmentController as AdminEnrollmentController;
use App\Http\Controllers\Admin\LecturerAccountController;
use App\Http\Controllers\Admin\StudentAccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Lecturer\CourseController as LecturerCourseController;
use App\Http\Controllers\Student\CourseController as StudentCourseController;
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

    Route::middleware('role:mahasiswa')->group(function () {
        Route::get('/mahasiswa/dashboard', [DashboardController::class, 'show'])->name('mahasiswa.dashboard');
        Route::get('/mahasiswa/courses', [StudentCourseController::class, 'index'])->name('mahasiswa.courses.index');
        Route::get('/mahasiswa/courses/{course}', [StudentCourseController::class, 'show'])->name('mahasiswa.courses.show');
    });

    Route::middleware('role:guru_dosen')->group(function () {
        Route::get('/guru-dosen/dashboard', [DashboardController::class, 'show'])->name('lecturer.dashboard');
        Route::get('/guru-dosen/courses', [LecturerCourseController::class, 'index'])->name('lecturer.courses.index');
        Route::get('/guru-dosen/courses/{course}', [LecturerCourseController::class, 'show'])->name('lecturer.courses.show');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', [DashboardController::class, 'show'])->name('admin.dashboard');

        Route::get('/admin/students', [StudentAccountController::class, 'index'])->name('admin.students.index');
        Route::get('/admin/students/{student}', [StudentAccountController::class, 'show'])->name('admin.students.show');
        Route::patch('/admin/students/{student}/status', [StudentAccountController::class, 'updateStatus'])->name('admin.students.status');
        Route::post('/admin/students/{student}/temporary-password', [StudentAccountController::class, 'temporaryPassword'])
            ->name('admin.students.temporary-password');

        Route::get('/admin/courses', [AdminCourseController::class, 'index'])->name('admin.courses.index');
        Route::post('/admin/courses', [AdminCourseController::class, 'store'])->name('admin.courses.store');
        Route::get('/admin/courses/{course}/edit', [AdminCourseController::class, 'edit'])->name('admin.courses.edit');
        Route::put('/admin/courses/{course}', [AdminCourseController::class, 'update'])->name('admin.courses.update');
        Route::patch('/admin/courses/{course}/archive', [AdminCourseController::class, 'archive'])->name('admin.courses.archive');

        Route::get('/admin/enrollments', [AdminEnrollmentController::class, 'index'])->name('admin.enrollments.index');
        Route::post('/admin/enrollments', [AdminEnrollmentController::class, 'store'])->name('admin.enrollments.store');

        Route::get('/admin/lecturers', [LecturerAccountController::class, 'index'])->name('admin.lecturers.index');
        Route::post('/admin/lecturers', [LecturerAccountController::class, 'store'])->name('admin.lecturers.store');
        Route::post('/admin/lecturers/{lecturer}/temporary-password', [LecturerAccountController::class, 'resetTemporaryPassword'])
            ->name('admin.lecturers.temporary-password');
    });
});
