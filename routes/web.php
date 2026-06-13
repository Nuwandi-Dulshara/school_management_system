<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SuperAdmin\StudentManagementController;
use App\Http\Controllers\SuperAdmin\TeacherManagementController;
use App\Http\Controllers\SuperAdmin\UserManagementController;
use Illuminate\Support\Facades\Route;

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
Route::middleware(['auth', 'super_admin'])->prefix('dashboard/super-admin')->group(function () {
    Route::get('/', function () {
        return view('dashboards.super_admin');
    })->name('dashboard.super_admin');

    Route::get('/users', [UserManagementController::class, 'index'])->name('super_admin.users.index');
    Route::get('/users/create', [UserManagementController::class, 'create'])->name('super_admin.users.create');
    Route::post('/users', [UserManagementController::class, 'store'])->name('super_admin.users.store');
    Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('super_admin.users.edit');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('super_admin.users.update');
    Route::patch('/users/{user}/status', [UserManagementController::class, 'toggleStatus'])->name('super_admin.users.status');
    Route::put('/users/{user}/password', [UserManagementController::class, 'resetPassword'])->name('super_admin.users.password');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('super_admin.users.destroy');
    Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('super_admin.users.show');
    Route::get('/roles', [UserManagementController::class, 'roles'])->name('super_admin.roles.index');

    Route::get('/students/status', [StudentManagementController::class, 'status'])->name('super_admin.students.status');
    Route::patch('/students/{student}/status', [StudentManagementController::class, 'updateStatus'])->name('super_admin.students.status.update');
    Route::get('/students', [StudentManagementController::class, 'index'])->name('super_admin.students.index');
    Route::get('/students/create', [StudentManagementController::class, 'create'])->name('super_admin.students.create');
    Route::post('/students', [StudentManagementController::class, 'store'])->name('super_admin.students.store');
    Route::get('/students/{student}/edit', [StudentManagementController::class, 'edit'])->name('super_admin.students.edit');
    Route::put('/students/{student}', [StudentManagementController::class, 'update'])->name('super_admin.students.update');
    Route::delete('/students/{student}', [StudentManagementController::class, 'destroy'])->name('super_admin.students.destroy');
    Route::get('/students/{student}/guardians', [StudentManagementController::class, 'guardians'])->name('super_admin.students.guardians');
    Route::put('/students/{student}/guardians', [StudentManagementController::class, 'updateGuardian'])->name('super_admin.students.guardians.update');
    Route::get('/students/{student}/documents', [StudentManagementController::class, 'documents'])->name('super_admin.students.documents');
    Route::post('/students/{student}/documents', [StudentManagementController::class, 'storeDocument'])->name('super_admin.students.documents.store');
    Route::delete('/students/{student}/documents/{document}', [StudentManagementController::class, 'destroyDocument'])->name('super_admin.students.documents.destroy');
    Route::get('/students/{student}', [StudentManagementController::class, 'show'])->name('super_admin.students.show');

    Route::get('/teachers', [TeacherManagementController::class, 'index'])->name('super_admin.teachers.index');
    Route::get('/teachers/create', [TeacherManagementController::class, 'create'])->name('super_admin.teachers.create');
    Route::post('/teachers', [TeacherManagementController::class, 'store'])->name('super_admin.teachers.store');
    Route::get('/teachers/{teacher}/edit', [TeacherManagementController::class, 'edit'])->name('super_admin.teachers.edit');
    Route::put('/teachers/{teacher}', [TeacherManagementController::class, 'update'])->name('super_admin.teachers.update');
    Route::patch('/teachers/{teacher}/status', [TeacherManagementController::class, 'toggleStatus'])->name('super_admin.teachers.status');
    Route::delete('/teachers/{teacher}', [TeacherManagementController::class, 'destroy'])->name('super_admin.teachers.destroy');
    Route::get('/teachers/{teacher}/assigned-classes', [TeacherManagementController::class, 'assignedClasses'])->name('super_admin.teachers.classes');
    Route::post('/teachers/{teacher}/assigned-classes', [TeacherManagementController::class, 'storeAssignedClass'])->name('super_admin.teachers.classes.store');
    Route::put('/teachers/{teacher}/assigned-classes/{classAssignment}', [TeacherManagementController::class, 'updateAssignedClass'])->name('super_admin.teachers.classes.update');
    Route::delete('/teachers/{teacher}/assigned-classes/{classAssignment}', [TeacherManagementController::class, 'destroyAssignedClass'])->name('super_admin.teachers.classes.destroy');
    Route::get('/teachers/{teacher}/assigned-subjects', [TeacherManagementController::class, 'assignedSubjects'])->name('super_admin.teachers.subjects');
    Route::post('/teachers/{teacher}/assigned-subjects', [TeacherManagementController::class, 'storeAssignedSubject'])->name('super_admin.teachers.subjects.store');
    Route::put('/teachers/{teacher}/assigned-subjects/{subjectAssignment}', [TeacherManagementController::class, 'updateAssignedSubject'])->name('super_admin.teachers.subjects.update');
    Route::delete('/teachers/{teacher}/assigned-subjects/{subjectAssignment}', [TeacherManagementController::class, 'destroyAssignedSubject'])->name('super_admin.teachers.subjects.destroy');
    Route::get('/teachers/{teacher}', [TeacherManagementController::class, 'show'])->name('super_admin.teachers.show');
});

Route::middleware('auth')->group(function () {
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
