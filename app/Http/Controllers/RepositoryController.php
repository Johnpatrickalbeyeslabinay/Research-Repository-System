<?php

namespace App\Http\Controllers;

use App\Models\Sdg;
use App\Models\UserFile;
use App\Support\ResearchAccess;
use Illuminate\Support\Facades\Auth;

/**
 * Backs both /repository (any authenticated user) and /public (anonymous
 * visitors) — PublicController::index() just delegates to listing() here
 * with publicOnly: true. The two used to be ~95% duplicated controllers
 * with the same search/year-filter bugs copy-pasted in both places; kept
 * as one implementation so a fix here reaches both routes.
 */
class RepositoryController extends Controller
{
    public function index()
    {
        return $this->listing(publicOnly: false, view: 'repository');
    }

    public function listing(bool $publicOnly, string $view)
    {
        $filters = request()->only(['search', 'year', 'type', 'sdg', 'sort']);

        $studFiles = $this->baseQuery()
            // Nothing was eager-loaded before this — meta.blade.php touches
            // authors/keywords/sdgs on every row, so the listing would N+1
            // across the whole result set without this.
            ->with(['authors', 'keywords', 'sdgs'])
            ->tap(fn ($query) => $this->applyFilters($query, $filters))
            ->paginate(12)
            ->withQueryString();

        $years = $this->baseQuery()
            ->selectRaw('DISTINCT publication_year as year')
            ->whereNotNull('publication_year')
            ->orderByDesc('year')
            ->pluck('year');

        $types = $this->baseQuery()
            ->selectRaw('DISTINCT research_type as type')
            ->whereNotNull('research_type')
            ->orderBy('type')
            ->pluck('type');

        $sdgs = Sdg::orderBy('number')->get();

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
        ]);
    }

    public function show($id)
    {
        $file = UserFile::with(['authors', 'keywords', 'sdgs', 'adviserUser'])
            ->findOrFail($id);

        // Delegated to App\Support\ResearchAccess rather than re-deriving
        // the rule with a hardcoded approved+public filter, which was
        // STRICTER than the app's actual access rule and contradicted every
        // other surface. A student opening their own pending submission —
        // or an adviser opening one they're reviewing, or an admin opening
        // anything — got a 404 for research they are explicitly allowed to
        // read. The AI assistant made that visible: it correctly cites
        // research the asking user may access (VectorSearchService applies
        // this same predicate inside its SQL), and every one of those
        // citations linked here and 404'd.
        //
        // This does NOT widen public access: ResearchAccess::allows(null,
        // $file) is true only for approved+public, so an anonymous visitor
        // still sees exactly what they saw before, and a guessed id still
        // can't reach anyone else's private research.
        abort_unless(ResearchAccess::allows(Auth::user(), $file), 404);

        // /research/{id} is not behind the 'auth' middleware (see
        // routes/web.php) — /public visitors are anonymous by design.
        // Both file.view AND file.download are auth-only, so an
        // anonymous visitor gets no working PDF link on this page at
        // all (the old repository/public pages had this same problem —
        // a "View" button that silently bounced a logged-out visitor to
        // /login). Show a sign-in prompt instead.
        $canAccessPdf = Auth::check();

        return view('research.show', compact('file', 'canAccessPdf'));
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

    private function baseQuery()
    {
        // Private/pending research is hidden from the repository
        // entirely — the previous version of this method deliberately
        // showed all *approved* research regardless of visibility,
        // reasoning that /repository is for logged-in users only. But
        // the route has no role gate: any authenticated student sees
        // every other student's private-but-approved title, abstract,
        // and tags. PublicController's publicOnly flag is now redundant
        // with this (both routes end up with the same filter) — kept as
        // an explicit parameter so a future change to one surface's
        // visibility rules doesn't accidentally change the other's.
        return UserFile::where('status', 'approved')->where('visibility', 'public');
    }
}
