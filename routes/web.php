<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\ResearchAnalysisController;
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

// Role-agnostic authenticated actions — same for student/instructor/admin,
// so kept in one shared group rather than tripled across the three
// role-scoped groups below.
Route::middleware('auth')->group(function () {
    Route::post('/account/password', [AccountController::class, 'updatePassword'])
        ->name('account.password.update');

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])
        ->name('notifications.read');

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])
        ->name('notifications.read-all');
});

// AI research assistant — authenticated users only (no anonymous /public
// chat: see the project's RAG plan for why). Permission enforcement for
// what research the chatbot may draw on happens inside RetrievalService/
// VectorSearchService (App\Support\ResearchAccess), not here.
Route::middleware('auth')->prefix('chat')->name('chat.')->group(function () {
    Route::get('/conversations', [ChatController::class, 'index'])
        ->name('index');

    Route::post('/conversations', [ChatController::class, 'store'])
        ->name('store');

    Route::get('/conversations/{conversation}', [ChatController::class, 'show'])
        ->name('show');

    Route::delete('/conversations/{conversation}', [ChatController::class, 'destroy'])
        ->name('destroy');

    // The one endpoint that costs real API tokens — throttled tighter than
    // the submission flow's AI route (throttle:10,1) plus a per-user daily
    // quota (App\Http\Middleware\ChatDailyQuota) on top of the burst limit.
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'message'])
        ->middleware(['throttle:6,1', 'chat.quota'])
        ->name('message');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])
        ->name('admin');

    Route::post('/admin/store', [AdminController::class, 'store'])
        ->name('admin.store');

    Route::post('/admin/students/import', [AdminController::class, 'importStudents'])
        ->name('admin.students.import');

    Route::patch('/admin/users/{id}/toggle-status', [AdminController::class, 'toggleStatus'])
        ->name('admin.users.toggle-status');

    Route::patch('/admin/users/{id}', [AdminController::class, 'updateUser'])
        ->name('admin.users.update');

    // ADMIN ACTION
    Route::post('/research/{id}/approve', [AdminController::class, 'approve'])
        ->name('research.approve');

    Route::post('/research/{id}/reject', [AdminController::class, 'reject'])
        ->name('research.reject');
});

// student routes
Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/student', [StudentController::class, 'index'])
        ->name('student');

    Route::post('/student/store', [StudentController::class, 'store'])
        ->name('student.store');

    // research update
    Route::put('/student/submission/{id}', [StudentController::class, 'update'])
        ->name('student.update');

    // Deterministic PDF validation/extraction preview (no AI, no DB write) —
    // called right after a file is selected to pre-fill the submission form.
    Route::post('/research/pdf/analyze', [StudentController::class, 'analyzePdf'])
        ->middleware('throttle:20,1')
        ->name('research.pdf.analyze');

    // AI-assisted keyword/SDG suggestions from title + abstract only —
    // the one generative-AI call in the submission flow. Throttled tighter
    // than the PDF preview since it costs real API tokens.
    Route::post('/research/analyze', [ResearchAnalysisController::class, 'analyze'])
        ->middleware('throttle:10,1')
        ->name('research.analyze');
});

// instructor routes
Route::middleware(['auth', 'role:instructor'])->group(function () {
    Route::get('/instructor', [InstructorController::class, 'index'])
        ->name('instructor');

    // INSTRUCTOR ACTION
    Route::post('/instructor/research/{id}/approve', [InstructorController::class, 'approve'])
        ->name('instructor.research.approve');

    Route::post('/instructor/research/{id}/reject', [InstructorController::class, 'reject'])
        ->name('instructor.research.reject');

    Route::post('/instructor/research/{id}/feedback', [InstructorController::class, 'feedback'])
        ->name('instructor.research.feedback');
});

// home
Route::get('/home', [HomeController::class, 'index'])->name('home');

// public routes

Route::get('/public', [PublicController::class, 'index'])
    ->name('public');

// view route
Route::get('/view/{id}', [StudentController::class, 'view'])
    ->middleware('auth')
    ->name('file.view');

// download route
Route::get('/download/{id}', [StudentController::class, 'download'])
    ->middleware('auth')
    ->name('file.download');

// Repository route
Route::get('/repository', [RepositoryController::class, 'index'])
    ->middleware('auth')
    ->name('repository');

// Research detail page — deliberately NOT behind 'auth': /public is
// anonymous, and its old "View" button pointed at the auth-only
// file.view, silently bouncing a logged-out visitor to /login. The page
// itself is public for approved+public research; the PDF stays gated
// (RepositoryController::show() only ever loads approved+public rows,
// and the Download link/prompt on the page respects Auth::check()).
Route::get('/research/{id}', [RepositoryController::class, 'show'])
    ->name('research.show');
