<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// Home page
Route::get('/', function () {
    return view('home', [
        'name' => 'Yshacute',
    ]);
});

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.submit');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');

// Admin routes
Route::get('/admin', [AdminController::class, 'index'])
    ->name('admin');

Route::post('/admin/store', [AdminController::class, 'store'])
    ->name('admin.store');

Route::get('/admin_dashboard', [AdminController::class, 'index'])
    ->middleware(['auth']);

// student routes

Route::get('/student', [StudentController::class, 'index'])
    ->name('student');

Route::post('/student/store', [StudentController::class, 'store'])
    ->name('student.store');

// instructor routes
Route::get('/instructor', [InstructorController::class, 'index'])
    ->name('instructor');

Route::post('/instructor/store', [InstructorController::class, 'store'])
    ->name('instructor.store');

// home
Route::get('/home', [HomeController::class, 'index'])->name('home');

// public routes

Route::get('/public', [PublicController::class, 'index'])
    ->name('public');

// view route
Route::get('/view/{id}', [StudentController::class, 'view'])
    ->name('file.view');

// download route
Route::get('/download/{id}', [StudentController::class, 'download'])
    ->name('file.download');

// ACTION ADMIN
Route::post('/research/{id}/approve', [AdminController::class, 'approve'])->name('research.approve');

Route::post('/research/{id}/reject', [AdminController::class, 'reject'])->name('research.reject');

// Repository route
Route::get('/repository', [RepositoryController::class, 'index'])
    ->middleware('auth')
    ->name('repository');

//route::get('send-mail', [AdminController::class, 'repositoryMail']);
