<?php

namespace App\Http\Controllers;

use App\Models\Sdg;
use App\Models\User;
use App\Models\UserFile;
use App\Support\ResearchAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Backs both /repository (any authenticated user) and /public (anonymous
 * visitors) — PublicController::index() just delegates to listing() here
 * with publicOnly: true. The two used to be ~95% duplicated controllers
 * with the same search/year-filter bugs copy-pasted in both places; kept
 * as one implementation so a fix here reaches both routes.
 */
class RepositoryController extends Controller
{
    private const FACETS_CACHE_KEY = 'repository_facets';

    private const FACETS_CACHE_TTL = 600;

    /**
     * Called wherever a write can change what's approved+public, so a
     * newly approved paper's year/type shows up in the filters at once.
     */
    public static function forgetFacets(): void
    {
        Cache::forget(self::FACETS_CACHE_KEY);
        Cache::forget(self::FACETS_CACHE_KEY.':public');
    }

    public function index()
    {
        return $this->listing(publicOnly: false, view: 'repository');
    }

    public function listing(bool $publicOnly, string $view)
    {
        $filters = request()->only(['search', 'year', 'type', 'sdg', 'sort']);
        $viewer = $publicOnly ? null : Auth::user();

        $studFiles = $this->baseQuery($publicOnly, $viewer)
            // Nothing was eager-loaded before this — meta.blade.php touches
            // authors/keywords/sdgs on every row, so the listing would N+1
            // across the whole result set without this.
            ->with(['authors', 'keywords', 'sdgs'])
            ->tap(fn ($query) => $this->applyFilters($query, $filters))
            ->paginate(12)
            ->withQueryString();

        $loadFacets = fn () => [
            'years' => $this->baseQuery($publicOnly, $viewer)
                ->selectRaw('DISTINCT publication_year as year')
                ->whereNotNull('publication_year')
                ->orderByDesc('year')
                ->pluck('year'),
            'types' => $this->baseQuery($publicOnly, $viewer)
                ->selectRaw('DISTINCT research_type as type')
                ->whereNotNull('research_type')
                ->orderBy('type')
                ->pluck('type'),
            'sdgs' => Sdg::query()
                ->whereHas('userFiles', fn ($query) => $this->constrainRepositoryQuery($query, $publicOnly, $viewer))
                ->orderBy('number')
                ->get(),
        ];

        // Public facets are shared and safe to cache globally. Authenticated
        // facets depend on row-level access, so calculating them per request
        // avoids leaking another user's private years or research types.
        ['years' => $years, 'types' => $types, 'sdgs' => $sdgs] = $publicOnly
            ? Cache::remember(self::FACETS_CACHE_KEY.':public', self::FACETS_CACHE_TTL, $loadFacets)
            : $loadFacets();

        return view($view, [
            'studFiles' => $studFiles,
            'years' => $years,
            'types' => $types,
            'sdgs' => $sdgs,
            'search' => $filters['search'] ?? '',
            'year' => $filters['year'] ?? '',
            'type' => $filters['type'] ?? '',
            'sdg' => $filters['sdg'] ?? '',
            'sort' => $filters['sort'] ?? 'recent',
            'showDownload' => ! $publicOnly,
            'isPublicRepository' => $publicOnly,
        ]);
    }

    // public function show($id)
    // {
    // $file = UserFile::with(['authors', 'keywords', 'sdgs', 'adviserUser'])
    // ->findOrFail($id);

    // abort_unless(ResearchAccess::allows(Auth::user(), $file), 404);

    // The legacy /research/{id} URL is also intentionally public for an
    // approved public record. Anonymous requests therefore use the same
    // metadata-only shell and actions as /public/research/{id}.
    // $isPublicView = request()->routeIs('public.research.show') || Auth::guest();

    // Public viewing must never show PDF/download access,
    // even if the visitor happens to be logged in.
    // $canAccessPdf = Auth::check() && ! $isPublicView;
    // $backUrl = $this->repositoryBackUrl($isPublicView);

    // return view('research.show', compact(
    // 'file', 'canAccessPdf', 'isPublicView','backUrl' ));}

    public function show($id)
    {
        $file = UserFile::with([
            'authors',
            'keywords',
            'sdgs',
            'adviserUser',
        ])->findOrFail($id);

        // Public view
        $isPublicView = request()->routeIs('public.research.show')
            || Auth::guest();

        /*
        |--------------------------------------------------------------------------
        | PUBLIC VIEWING
        |--------------------------------------------------------------------------
        | Keep the existing public behavior:
        | only approved + public research can be viewed.
        */

        if ($isPublicView) {
            abort_unless(
                $file->status === 'approved' &&
                $file->visibility === 'public',
                404
            );

            $requesterEmail = session(
                'research_access_email_'.$file->id
            );

            $accessRequest = null;

            if ($requesterEmail) {
                $accessRequest = \App\Models\AccessRequest::query()
                    ->where('stud_file_id', $file->id)
                    ->where('email', $requesterEmail)
                    ->latest()
                    ->first();
            }

            $hasApprovedAccess =
                $accessRequest?->status === 'approved';

            $hasPendingAccessRequest =
                $accessRequest?->status === 'pending';

            $canAccessPdf = $hasApprovedAccess;
        }

        /*
        |--------------------------------------------------------------------------
        | AUTHENTICATED REPOSITORY
        |--------------------------------------------------------------------------
        | Registered users can view the research information.
        | Actual PDF/download access still requires authorization.
        */

        else {

            abort_unless(
                $file->status === 'approved',
                404
            );

            $accessRequest = \App\Models\AccessRequest::query()
                ->where('stud_file_id', $file->id)
                ->where('email', Auth::user()->email)
                ->latest()
                ->first();

            // $hasApprovedAccess =
            // $file->visibility === 'public'
            // || ResearchAccess::allows(Auth::user(), $file)
            // || $accessRequest?->status === 'approved';

            $isDirectAuthorizedUser =
            Auth::user()->role === 'admin'
            || $file->user_id === Auth::id()
            || $file->adviser_id === Auth::id()
            || $file->authors->contains(
                fn ($author) => $author->user_id === Auth::id()
            );

            $hasApprovedAccess =
                $isDirectAuthorizedUser
                || $accessRequest?->status === 'approved';

            $hasPendingAccessRequest =
                $accessRequest?->status === 'pending';

            $canAccessPdf = $hasApprovedAccess;
        }

        $backUrl = $this->repositoryBackUrl($isPublicView);

        return view('research.show', compact(
            'file',
            'canAccessPdf',
            'isPublicView',
            'backUrl',
            'hasApprovedAccess',
            'hasPendingAccessRequest',
        ));
    }

    private function applyFilters($query, array $filters): void
    {
        // Was title/desc/auto_tag/name only, so the search box's own
        // placeholder ("title, author, tag, or keywords") was a false
        // promise: a co-author recorded in stud_file_author and a
        // curated keyword in the keywords table were both unmatchable.
        if ($search = $filters['search'] ?? null) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('desc', 'ilike', "%{$search}%")
                    ->orWhere('auto_tag', 'ilike', "%{$search}%")
                    ->orWhere('name', 'ilike', "%{$search}%")
                    ->orWhereHas('authors', fn ($a) => $a->where('author_name', 'ilike', "%{$search}%"))
                    ->orWhereHas('keywords', fn ($k) => $k->where('display_name', 'ilike', "%{$search}%"));
            });
        }

        // Was whereYear('created_at') — the submission date — while every
        // view labelled the filter/column "Year Published". A 2019 thesis
        // uploaded in 2026 was filed under 2026.
        if ($year = $filters['year'] ?? null) {
            $query->where('publication_year', $year);
        }

        if ($type = $filters['type'] ?? null) {
            $query->where('research_type', $type);
        }

        if ($sdg = $filters['sdg'] ?? null) {
            $query->whereHas('sdgs', fn ($s) => $s->where('sdgs.id', $sdg));
        }

        // There was no ordering at all before this — Postgres returned
        // whatever physical row order it felt like, so the repository had
        // no real "newest first".
        match ($filters['sort'] ?? 'recent') {
            'views' => $query->orderByDesc('views'),
            'downloads' => $query->orderByDesc('downloads'),
            default => $query->latest(),
        };
    }

    private function baseQuery(bool $publicOnly, ?User $viewer)
    {
        return $this->constrainRepositoryQuery(
            UserFile::query(),
            $publicOnly,
            $viewer
        );
    }

    private function constrainRepositoryQuery($query, bool $publicOnly, ?User $viewer)
    {
        $query->where('status', 'approved');

        if ($publicOnly) {
            return $query->where('visibility', 'public');
        }

        // Authenticated Repository:
        // Show all approved research, including private research.
        // PDF/download access is checked separately.
        return $query;
    }

    private function repositoryBackUrl(bool $isPublicView): string
    {
        return $isPublicView
            ? route('public')
            : route('repository');
    }
}
