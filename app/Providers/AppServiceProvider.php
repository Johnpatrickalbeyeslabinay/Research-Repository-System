<?php

namespace App\Providers;

use App\Services\Chat\ChatTurnService;
use App\Services\Chat\Handlers\CatalogCountHandler;
use App\Services\Chat\Handlers\CatalogHandler;
use App\Services\Chat\Handlers\ExternalLiteratureHandler;
use App\Services\Chat\Handlers\IdentityHandler;
use App\Services\Chat\Handlers\OutOfScopeHandler;
use App\Services\Chat\Handlers\PlatformHandler;
use App\Services\Chat\Handlers\ResearchQaHandler;
use App\Services\Chat\Handlers\SmallTalkHandler;
use App\Services\Chat\Routing\Classifiers\CatalogClassifier;
use App\Services\Chat\Routing\Classifiers\ExternalLiteratureClassifier;
use App\Services\Chat\Routing\Classifiers\IdentityClassifier;
use App\Services\Chat\Routing\Classifiers\OutOfScopeClassifier;
use App\Services\Chat\Routing\Classifiers\PlatformClassifier;
use App\Services\Chat\Routing\Classifiers\SmallTalkClassifier;
use App\Services\Chat\Routing\IntentRouter;
use App\Services\External\LiteratureProvider;
use App\Services\External\NullLiteratureProvider;
use App\Services\External\OpenAlexProvider;
use App\Support\ChatLlmCallTracker;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
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

        // The classifier chain behind App\Services\Chat\Routing\IntentRouter.
        // Registration order here is deliberately NOT the routing order — the
        // router sorts by each classifier's priority() — so this list can be
        // reordered or appended to without changing behaviour. Precedence is
        // asserted in tests/Unit/IntentRouterTest.php.
        $this->app->tag([
            SmallTalkClassifier::class,           // 10 — free; only whole-message small talk
            IdentityClassifier::class,            // 20 — free; must beat Platform
            PlatformClassifier::class,            // 30
            OutOfScopeClassifier::class,          // 40 — free; must beat Catalog
            ExternalLiteratureClassifier::class,  // 50 — must beat Catalog
            CatalogClassifier::class,             // 60
        ], 'chat.classifiers');

        // The external index, resolved from config so "off" is the inert
        // default: with EXTERNAL_LITERATURE_DRIVER unset the app makes no
        // outbound calls and behaves exactly as it did before the feature
        // existed. A student records system calling out on a user's question
        // should be a deployment decision, not a default.
        $this->app->bind(LiteratureProvider::class, function () {
            return match ((string) config('external_literature.driver', 'null')) {
                'openalex' => new OpenAlexProvider,
                default => new NullLiteratureProvider,
            };
        });

        $this->app->when(IntentRouter::class)
            ->needs('$classifiers')
            ->give(fn ($app) => $app->tagged('chat.classifiers'));

        // Turn handlers. Order MATTERS here, unlike the classifiers above:
        // ChatTurnService keeps the first handler registered for an intent, and
        // ResearchQaHandler deliberately also claims ExternalLiterature as a
        // fallback for when that feature is disabled. Listing
        // ExternalLiteratureHandler first is what lets it win when enabled,
        // while a deployment with the feature off still answers the question
        // from the local corpus instead of refusing it.
        $this->app->tag([
            SmallTalkHandler::class,
            IdentityHandler::class,
            OutOfScopeHandler::class,
            PlatformHandler::class,
            CatalogCountHandler::class,
            CatalogHandler::class,
            ExternalLiteratureHandler::class,
            ResearchQaHandler::class,
        ], 'chat.handlers');

        $this->app->when(ChatTurnService::class)
            ->needs('$handlers')
            ->give(fn ($app) => $app->tagged('chat.handlers'));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Turbo Drive (resources/js/app.js) merges each new page's <head>
        // into the current one. 'reload' on the shared bundles makes a new
        // deploy — new hashed filenames — force a full page load instead of
        // mixing old JS with new markup. The per-layout stylesheets are
        // 'dynamic' so Turbo drops them when navigating to a page with a
        // different layout, rather than letting the student layout's rules
        // leak onto the repository page.
        $turboTrack = fn ($src) => [
            'data-turbo-track' => str_starts_with((string) $src, 'resources/css/layouts/') ? 'dynamic' : 'reload',
        ];
        Vite::useStyleTagAttributes($turboTrack);
        Vite::useScriptTagAttributes($turboTrack);

        // Every view in this app is Bootstrap 5 (bundled, no Tailwind except
        // welcome.blade.php) — without this, {{ $paginator->links() }}
        // renders Laravel's default Tailwind pagination markup, which
        // has no matching CSS loaded anywhere and displays unstyled.
        Paginator::useBootstrapFive();

        // The chat message route used to hardcode throttle:6,1 while
        // config/chat.php claimed 'per_minute' backed it — so raising the
        // burst limit via CHAT_RATE_PER_MINUTE changed nothing and the
        // config comment was a lie. Named limiter so the config is the only
        // place the number lives.
        //
        // Keyed on user id, falling back to IP: throttle:6,1 already keyed
        // on the authenticated user for this route, so the semantics are
        // unchanged. The route is behind 'auth', making the IP branch
        // unreachable in practice — it is there so the limiter stays
        // correct if it is ever reused on a guest route.
        RateLimiter::for('chat', fn (Request $request) => Limit::perMinute(
            (int) config('chat.limits.per_minute', 6)
        )->by($request->user()?->id ?: $request->ip()));
    }
}
