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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

// Home page
Route::get('/', function () {
    return view('home', [
        'name' => 'Yshacute',
    ]);
});

/*
 * Serverless queue drain.
 *
 * The app deploys to Vercel (vercel.json), which runs no long-lived
 * process — so `php artisan queue:work` never runs in production and a
 * dispatched App\Jobs\ProcessResearchDocument sits in the jobs table
 * forever. That is not hypothetical: it is exactly how the corpus came to
 * have zero embedded chunks while every submission looked successful, and
 * why the chatbot answered "I couldn't find enough information" to
 * everything.
 *
 * RESEARCH_INDEX_SYNC=true handles the common case inline via
 * ->afterResponse(). This endpoint is the safety net for the rest: a large
 * PDF that outruns the request, or a job that failed and was retried.
 * Point Vercel Cron (see vercel.json) or any external pinger at it.
 *
 * NOTE: Vercel's Hobby plan allows only ONE cron invocation per day. The
 * hourly schedule in vercel.json needs a Pro plan; on Hobby, either accept
 * daily catch-up or point an external scheduler (cron-job.org, GitHub
 * Actions) at this URL instead. Either way index_sync, not this, is what
 * makes indexing feel immediate.
 *
 * Deliberately NOT behind 'auth' — a cron has no session — so the shared
 * secret IS the authentication. hash_equals() to compare in constant time;
 * refuses outright when the secret is unset, so an unconfigured deployment
 * fails closed rather than exposing an open queue runner.
 */
Route::match(['get', 'post'], '/internal/queue/drain', function (Request $request) {
    $expected = (string) config('research.cron_secret');

    abort_if($expected === '', 404);

    // Two accepted forms, because the caller isn't always ours:
    //   - Authorization: Bearer <secret>  — what Vercel Cron sends (it
    //     also uses GET, hence the match() above rather than post()).
    //   - X-Cron-Secret: <secret>         — for curl / any other pinger.
    $bearer = (string) $request->bearerToken();
    $header = (string) $request->header('X-Cron-Secret');

    abort_unless(
        hash_equals($expected, $bearer) || hash_equals($expected, $header),
        403
    );

    Artisan::call('queue:work', [
        '--stop-when-empty' => true,
        // Comfortably inside a serverless function's ceiling — whatever is
        // left is picked up by the next cron tick rather than being killed
        // mid-job.
        '--max-time' => 50,
        '--tries' => 3,
    ]);

    return response()->json([
        'success' => true,
        'output' => trim(Artisan::output()),
    ]);
})->name('internal.queue.drain');

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
    //
    // 'throttle:chat' is the named limiter registered in
    // AppServiceProvider::boot(), which reads config('chat.limits.per_minute').
    // It replaced a hardcoded throttle:6,1 that ignored that config entirely.
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'message'])
        ->middleware(['throttle:chat', 'chat.quota'])
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
