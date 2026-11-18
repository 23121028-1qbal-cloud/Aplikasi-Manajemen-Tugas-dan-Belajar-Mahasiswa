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
use App\Http\Controllers\Student\PersonalTaskController;
use App\Http\Controllers\Student\StudyActivityController;
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

        Route::get('/mahasiswa/tasks', [PersonalTaskController::class, 'index'])->name('mahasiswa.tasks.index');
        Route::get('/mahasiswa/tasks/personal/create', [PersonalTaskController::class, 'create'])->name('mahasiswa.tasks.personal.create');
        Route::post('/mahasiswa/tasks/personal', [PersonalTaskController::class, 'store'])->name('mahasiswa.tasks.personal.store');
        Route::get('/mahasiswa/tasks/personal/{task}', [PersonalTaskController::class, 'show'])->name('mahasiswa.tasks.personal.show');
        Route::get('/mahasiswa/tasks/personal/{task}/edit', [PersonalTaskController::class, 'edit'])->name('mahasiswa.tasks.personal.edit');
        Route::put('/mahasiswa/tasks/personal/{task}', [PersonalTaskController::class, 'update'])->name('mahasiswa.tasks.personal.update');
        Route::patch('/mahasiswa/tasks/personal/{task}/archive', [PersonalTaskController::class, 'archive'])->name('mahasiswa.tasks.personal.archive');
        Route::get('/mahasiswa/task-history', [PersonalTaskController::class, 'history'])->name('mahasiswa.task-history');

        Route::get('/mahasiswa/study-activities', [StudyActivityController::class, 'index'])->name('mahasiswa.study-activities.index');
        Route::get('/mahasiswa/study-activities/create', [StudyActivityController::class, 'create'])->name('mahasiswa.study-activities.create');
        Route::post('/mahasiswa/study-activities', [StudyActivityController::class, 'store'])->name('mahasiswa.study-activities.store');
        Route::get('/mahasiswa/study-activities/{activity}', [StudyActivityController::class, 'show'])->name('mahasiswa.study-activities.show');
        Route::get('/mahasiswa/study-activities/{activity}/edit', [StudyActivityController::class, 'edit'])->name('mahasiswa.study-activities.edit');
        Route::put('/mahasiswa/study-activities/{activity}', [StudyActivityController::class, 'update'])->name('mahasiswa.study-activities.update');
        Route::patch('/mahasiswa/study-activities/{activity}/archive', [StudyActivityController::class, 'archive'])->name('mahasiswa.study-activities.archive');
        Route::get('/mahasiswa/study-history', [StudyActivityController::class, 'history'])->name('mahasiswa.study-history');
    });

    Route::middleware('role:guru_dosen')->group(function () {
        Route::get('/guru-dosen/dashboard', [DashboardController::class, 'show'])->name('lecturer.dashboard');
        Route::get('/guru-dosen/courses', [LecturerCourseController::class, 'index'])->name('lecturer.courses.index');
        Route::get('/guru-dosen/courses/{course}', [LecturerCourseController::class, 'show'])->name('lecturer.courses.show');
        Route::put('/guru-dosen/courses/{course}', [LecturerCourseController::class, 'update'])->name('lecturer.courses.update');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', [DashboardController::class, 'show'])->name('admin.dashboard');

        Route::get('/admin/students', [StudentAccountController::class, 'index'])->name('admin.students.index');
        Route::get('/admin/students/{student}', [StudentAccountController::class, 'show'])->name('admin.students.show');
        Route::put('/admin/students/{student}', [StudentAccountController::class, 'update'])->name('admin.students.update');
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
        Route::put('/admin/enrollments/{enrollment}', [AdminEnrollmentController::class, 'update'])->name('admin.enrollments.update');

        Route::get('/admin/lecturers', [LecturerAccountController::class, 'index'])->name('admin.lecturers.index');
        Route::post('/admin/lecturers', [LecturerAccountController::class, 'store'])->name('admin.lecturers.store');
        Route::get('/admin/lecturers/{lecturer}', [LecturerAccountController::class, 'show'])->name('admin.lecturers.show');
        Route::put('/admin/lecturers/{lecturer}', [LecturerAccountController::class, 'update'])->name('admin.lecturers.update');
        Route::post('/admin/lecturers/{lecturer}/temporary-password', [LecturerAccountController::class, 'resetTemporaryPassword'])
            ->name('admin.lecturers.temporary-password');
    });
});
