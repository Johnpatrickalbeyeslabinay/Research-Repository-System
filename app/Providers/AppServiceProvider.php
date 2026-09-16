<?php

namespace App\Providers;

use App\Support\ChatLlmCallTracker;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One instance per request — lets ChatController tell
        // App\Http\Middleware\ChatDailyQuota that a turn actually reached
        // the LLM without going through $request->attributes, which a
        // FormRequest-typed controller parameter can't reliably signal
        // back to middleware (see ChatLlmCallTracker's docblock).
        $this->app->singleton(ChatLlmCallTracker::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Every view in this app is Bootstrap 5 (CDN, no Tailwind except
        // welcome.blade.php) — without this, {{ $paginator->links() }}
        // renders Laravel's default Tailwind pagination markup, which
        // has no matching CSS loaded anywhere and displays unstyled.
        Paginator::useBootstrapFive();
    }
}
