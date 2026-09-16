<?php

use App\Http\Middleware\ChatDailyQuota;
use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
            'chat.quota' => ChatDailyQuota::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // When a multipart upload exceeds php.ini's post_max_size, PHP
        // discards the entire request body — CSRF token included — before
        // Laravel ever sees it. That otherwise surfaces as a bare 419 Page
        // Expired, which reads as "the site is broken" rather than "your
        // file is too large." Give it the same message a file.max
        // validation failure would.
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $message = 'That file is too large to upload. Please choose a smaller PDF.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 413);
            }

            return back()->withErrors(['file' => $message])->withInput($request->except('file'));
        });
    })->create();
