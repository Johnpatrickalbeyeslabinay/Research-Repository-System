<?php

namespace App\Http\Controllers;

use App\Exceptions\PdfProcessingException;
use App\Http\Requests\StoreResearchRequest;
use App\Http\Requests\UpdateResearchRequest;
use App\Jobs\ProcessResearchDocument;
use App\Models\AccessRequest;
use App\Models\Keyword;
use App\Models\Sdg;
use App\Models\User;
use App\Models\UserFile;
use App\Notifications\ResearchSubmitted;
use App\Services\Ai\AbstractGenerationService;
use App\Services\Ai\MetadataAnalysisService;
use App\Services\Pdf\PdfValidationService;
use App\Services\Pdf\SectionDetector;
use App\Support\ResearchAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    // Research PDFs always live on the same private disk, regardless of
    // the research's visibility/approval status — access is governed
    // entirely by authorizeFileAccess() below, not by which disk a file
    // happens to sit on. This avoids having to keep filesystem placement in
    // sync with every status/visibility transition.
    //
    // Which disk that is comes from config('research.storage_disk') rather
    // than being hardcoded — see that config file for why 'local' isn't
    // safe in production on this app's deployment target.
    private function disk(): string
    {
        return config('research.storage_disk', 'local');
    }

    public function __construct(
        private PdfValidationService $pdfValidation,
        private SectionDetector $sectionDetector,
        private MetadataAnalysisService $metadataAnalysis,
        private AbstractGenerationService $abstractGeneration,
    ) {}

    /**
     * Where the submitted abstract came from, decided server-side rather
     * than from a client flag: 'ai_generated' when it is (a lightly edited)
     * AbstractGenerationService draft for this PDF, 'extracted' when it is
     * the abstract SectionDetector found in the PDF, otherwise 'manual'.
     */
    private function abstractSource(?string $pdfHash, ?string $desc, ?string $extractedAbstract): string
    {
        if ($pdfHash !== null && $this->abstractGeneration->matchesGenerated($pdfHash, $desc)) {
            return 'ai_generated';
        }

        $normalize = fn (?string $text) => mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) $text)));

        if ($extractedAbstract !== null && $normalize($extractedAbstract) !== '' && $normalize($extractedAbstract) === $normalize($desc)) {
            return 'extracted';
        }

        return 'manual';
    }

    /**
     * The AI metadata fingerprint/model are echoed back from a prior
     * /research/analyze call as hidden fields — never trusted at face value.
     * The fingerprint is recomputed here from the actually-submitted
     * title+abstract; only a match is persisted as "this AI analysis is
     * current for this content." A mismatch (title/abstract edited after
     * analyzing, or no analysis run at all) just leaves the audit fields
     * unset — the keywords/SDGs the user is submitting are unaffected
     * either way, since those come from the chip/checkbox state, not here.
     */
    private function applyAiAuditFields(UserFile $fileRecord, Request $request): void
    {
        $submittedFingerprint = $request->input('ai_analysis_fingerprint');

        if (empty($submittedFingerprint)) {
            return;
        }

        $actualFingerprint = $this->metadataAnalysis->fingerprint($fileRecord->title, $fileRecord->desc);

        if (! hash_equals($actualFingerprint, $submittedFingerprint)) {
            return; // stale — title/abstract changed since the AI call
        }

        $fileRecord->ai_analysis_status = 'completed';
        $fileRecord->ai_analysis_fingerprint = $actualFingerprint;
        $fileRecord->ai_model_used = $request->input('ai_model_used');
        $fileRecord->ai_analyzed_at = now();
        $fileRecord->save();
    }

    public function index()
    {
        $view = request('view', 'dashboard');

        // Every list and count on this page is a view of the same rows — the
        // student's own submissions — so they're fetched once and sliced in
        // PHP. This used to be 7 separate queries against the same WHERE
        // user_id = ?, each a full round trip to the remote database.
        $studFiles = UserFile::where('user_id', Auth::id())
            ->with(['user', 'authors', 'keywords', 'sdgs'])
            ->latest()
            ->get();

        $accessRequests = AccessRequest::with('file')
            ->where('status', 'pending')
            ->whereHas('file', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->latest()
            ->get();

        $notifications = $studFiles;
        $rejectedFiles = $studFiles->where('status', 'rejected')->values();

        $totalUploads = $studFiles->count();
        $pendingUploads = $studFiles->where('status', 'pending')->count();
        $approvedUploads = $studFiles->where('status', 'approved')->count();
        $rejectedUploads = $rejectedFiles->count();

        $sdgs = Sdg::cachedList();

        // For the "Research Adviser" select and "+ Add Researcher" picker —
        // only id/name are rendered, so that's all that's fetched.
        $people = User::whereIn('role', ['instructor', 'student'])
            ->where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        $instructors = $people->where('role', 'instructor')->values();
        $otherStudents = $people->where('role', 'student')->values();

        // Real in-app notification count (adviser feedback, instructor/admin
        // review decisions) for the bell icon — separate from $notifications
        // above, which is an existing, already-working pseudo-feed derived
        // from this student's own UserFile rows, not touched by this change.
        $unreadNotificationsCount = Auth::user()->unreadNotifications()->count();

        return view('student', compact(
            'view',
            'studFiles',
            'notifications',
            'rejectedFiles',
            'totalUploads',
            'pendingUploads',
            'approvedUploads',
            'rejectedUploads',
            'sdgs',
            'instructors',
            'otherStudents',
            'unreadNotificationsCount',
            'accessRequests',
        ));
    }

    public function store(StoreResearchRequest $request)
    {
        // $adviser = User::findOrFail($request->adviser_id);
        $adviserIds = collect($request->input('advisers', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $advisers = User::whereIn('id', $adviserIds)
            ->where('role', 'instructor')
            ->get();

        $advisers = $adviserIds
            ->map(fn ($id) => $advisers->firstWhere('id', $id))
            ->filter()
            ->values();

        if ($advisers->isEmpty()) {
            return back()
                ->withErrors([
                    'advisers' => 'Please select at least one research adviser.',
                ])
                ->withInput();
        }

        $adviserNames = $advisers
            ->pluck('name')
            ->implode(', ');

        $primaryAdviser = $advisers->first();

        //  Generate auto tags
        $title = strtolower($request->title);

        $stopWords = [
            'the',
            'a',
            'an',
            'and',
            'or',
            'to',
            'of',
            'in',
            'on',
            'for',
            'with',
            'at',
            'by',
            'is',
            'are',
        ];

        $tags = preg_replace('/[^a-z0-9\s]/', '', strtolower($request->title));
        $tags = explode(' ', $tags);
        $tags = array_filter($tags, fn ($word) => ! empty($word) && ! in_array($word, $stopWords));
        $tags = array_unique($tags);

        $tags = implode(',', $tags);

        // Validate the PDF's content (readable, unencrypted, has extractable
        // text) and extract it — deterministic parsing only, no AI, and this
        // happens before anything is persisted so a bad PDF never leaves a
        // half-created research record behind.
        $file = $request->file('file');

        try {
            $processed = $this->pdfValidation->process($file);
        } catch (PdfProcessingException $e) {
            return back()->withErrors(['file' => $e->getMessage()])->withInput();
        }

        $hash = $processed['hash'];
        $storagePath = "research_files/{$hash}.pdf";

        // detect duplicate thesis
        $duplicate = UserFile::where('pdf_hash', $hash)->first();

        if ($duplicate) {
            return back()
                ->withErrors([
                    'file' => 'This research document has already been uploaded.',
                ])
                ->withInput();
        }

        // Content-addressed filename: re-uploading byte-identical content
        // (e.g. a duplicate submission) reuses the existing stored file
        // instead of writing (and later re-processing) it again. A revised
        // PDF has a different hash and is stored as a new file, as normal.
        if (! Storage::disk($this->disk())->exists($storagePath)) {
            Storage::disk($this->disk())->putFileAs('research_files', $file, "{$hash}.pdf");
        }

        $detected = $this->sectionDetector->detectCached($processed['hash'], $processed['pages'], $processed['full_text']);

        // Save to database
        $fileRecord = UserFile::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'name' => Auth::user()->name,
            // 'adviser' => $adviser->name,
            // 'adviser_id' => $adviser->id,
            'adviser' => $adviserNames,
            'adviser_id' => $primaryAdviser->id,

            'research_type' => $request->research_type,
            'publication_year' => $request->publication_year,
            'desc' => $request->desc,
            'file_path' => $storagePath,
            'auto_tag' => $tags,
            'views' => 0,
            'downloads' => 0,
            'status' => 'pending',
            'visibility' => 'private',
            'pdf_hash' => $hash,
            'pdf_page_count' => $processed['page_count'],
            'extracted_abstract' => $detected['abstract']['text'],
            'abstract_source' => $this->abstractSource($hash, $request->desc, $detected['abstract']['text']),
            'extracted_sections' => $detected['sections'],
            'extracted_references' => $detected['references'],
        ]);

        $fileRecord->sdgs()->sync($request->sdg);
        $this->syncAuthors($fileRecord, $request->input('researchers', []));

        $this->syncKeywords($fileRecord, $request->input('keywords', []), $request->input('keyword_sources', []), $detected['keywords']);
        $this->applyAiAuditFields($fileRecord, $request);

        $this->dispatchIndexing($fileRecord->id);

        foreach ($advisers as $adviser) {
            self::forgetAdminDashboardCache($fileRecord, $adviser->id);
            $adviser->notify(new ResearchSubmitted($fileRecord));
        }

        return redirect()->route('student')->with('success', 'Research submitted successfully!');

    }

    /**
     * A new/updated submission changes the admin dashboard's upload count
     * for that year (AdminController) and this adviser's pending count
     * (InstructorController) — without busting the latter too, a fresh
     * submission could take up to a minute (DASHBOARD_CACHE_TTL) to show
     * up in the adviser's own queue.
     */
    private static function forgetAdminDashboardCache(UserFile $file, int $adviserId): void
    {
        Cache::forget('admin_dashboard_stats:'.$file->created_at->year);
        Cache::forget('instructor_dashboard_counts:'.$adviserId);
        RepositoryController::forgetFacets();
    }

    /**
     * Dispatch mode is config('research.index_sync') — see
     * ProcessResearchDocument's class docblock. Default: the real queue
     * (config('queue.default'), retried via the job's $tries/backoff).
     * RESEARCH_INDEX_SYNC=true: ->afterResponse(), running inline after
     * the response is sent with no retry, for environments with no
     * `queue:work` process.
     */
    private function dispatchIndexing(int $studFileId): void
    {
        if (config('research.index_sync')) {
            ProcessResearchDocument::dispatch($studFileId)->afterResponse();

            return;
        }

        ProcessResearchDocument::dispatch($studFileId);
    }

    /**
     * Persist the final keyword chip list submitted by the form, tagging
     * each as 'extracted' (matches a keyword this exact PDF's "Keywords:"
     * line produced), 'ai_suggested', or 'manual'.
     *
     * The server always has the final say on 'extracted' — re-matching
     * against the freshly re-extracted PDF keywords, never trusting the
     * client on that point. For the remaining two, only the client chip
     * UI knows whether a given keyword arrived via the AI suggestion tray
     * or was typed by hand; $submittedSources carries that (aligned by
     * index with $submittedKeywords — see keyword-input.blade.php's
     * keywords[] / keyword_sources[] hidden inputs), and is trusted only
     * to distinguish those two, since the server has no way to know
     * either way on its own.
     *
     * @param  string[]  $submittedKeywords  final chip list from the form
     * @param  string[]  $submittedSources  same length/order as $submittedKeywords
     * @param  string[]  $extractedKeywords  freshly re-extracted from the uploaded PDF
     */
    private function syncKeywords(UserFile $fileRecord, array $submittedKeywords, array $submittedSources, array $extractedKeywords): void
    {
        $extractedNormalized = array_map(fn ($k) => mb_strtolower(trim($k)), $extractedKeywords);

        $pivotData = [];

        foreach ($submittedKeywords as $i => $displayName) {
            $displayName = trim((string) $displayName);

            if ($displayName === '') {
                continue;
            }

            $keyword = Keyword::findOrCreateByDisplayName($displayName);

            if (in_array(mb_strtolower($displayName), $extractedNormalized, true)) {
                $source = 'extracted';
            } else {
                $clientSource = $submittedSources[$i] ?? 'manual';
                $source = $clientSource === 'ai_suggested' ? 'ai_suggested' : 'manual';
            }

            $pivotData[$keyword->id] = ['source' => $source];
        }

        $fileRecord->keywords()->sync($pivotData);
    }

    /**
     * Replace this submission's researcher list with the ordered rows the
     * form posted (see StoreResearchRequest's 'researchers.*.name' /
     * 'researchers.*.user_id'). A row is either linked to a real account
     * (user_id set, exact name match against a registered student — see
     * the researcher-input component) or a free-typed "guest" name with no
     * account at all; both are stored, since the DB column is nullable.
     *
     * Full delete-then-recreate rather than a diff/sync: the list is
     * small (max 20, validated), has no other data hanging off individual
     * rows yet, and this keeps the same simple pattern sdgs()->sync() and
     * syncKeywords() already use above.
     *
     * @param  array<int, array{name?: string, user_id?: int|string|null}>  $researchers
     */
    private function syncAuthors(UserFile $fileRecord, array $researchers): void
    {
        $fileRecord->authors()->delete();

        $seenUserIds = [];
        $seenNames = [];
        $position = 0;

        foreach ($researchers as $row) {
            $name = trim((string) ($row['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $userId = $row['user_id'] ?? null;
            $userId = $userId !== '' ? $userId : null;

            if ($userId !== null) {
                if (in_array($userId, $seenUserIds, true)) {
                    continue;
                }
                $seenUserIds[] = $userId;
            } else {
                $key = mb_strtolower($name);
                if (in_array($key, $seenNames, true)) {
                    continue;
                }
                $seenNames[] = $key;
            }

            $fileRecord->authors()->create([
                'user_id' => $userId,
                'author_name' => $name,
                'position' => $position++,
            ]);
        }
    }

    /**
     * Deterministic PDF validation + extraction preview, used to pre-fill
     * the submission form right after a file is selected — no database
     * writes, no generative AI. The actual store()/update() call re-runs
     * this against the final uploaded file rather than trusting this
     * preview, so a stale/tampered client-side state can't corrupt data.
     */
    public function analyzePdf(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf|max:'.(config('research.max_upload_mb', 10) * 1024),
        ], [
            'file.max' => 'The PDF file must not exceed '.config('research.max_upload_mb', 10).' MB.',
        ]);

        try {
            $processed = $this->pdfValidation->process($request->file('file'));
        } catch (PdfProcessingException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        /*
         * EXACT DUPLICATE CHECK
         *
         * The PDF hash is generated from the actual uploaded file.
         * If the same PDF already exists in UserFile, it is a duplicate.
         */
        $duplicate = UserFile::where('pdf_hash', $processed['hash'])->first();

        $detected = $this->sectionDetector->detectCached(
            $processed['hash'],
            $processed['pages'],
            $processed['full_text']
        );

        // No confidently-detected abstract: keep a bounded excerpt of the
        // paper server-side so the student can ask for an AI draft
        // (/research/abstract/generate) by sending back only this hash.
        // Nothing is generated here — that only happens on their click.
        $canGenerateAbstract = ($detected['abstract']['confidence'] ?? null) !== 'high';

        if ($canGenerateAbstract) {
            $excerpt = $this->sectionDetector->abstractSourceExcerpt($detected['sections'], $processed['full_text'], $processed['pages']);

            if ($excerpt !== null) {
                $this->abstractGeneration->storeSource($processed['hash'], $excerpt);
            } else {
                $canGenerateAbstract = false;
            }
        }

        return response()->json([
            'success' => true,

            'duplicate' => $duplicate !== null,

            'duplicate_message' => $duplicate
                ? 'This research document has already been uploaded. Please select a different PDF.'
                : null,

            'page_count' => $processed['page_count'],
            'abstract' => $detected['abstract'],
            'keywords' => $detected['keywords'],
            'fallback_excerpt' => $detected['fallback_excerpt'],
            'pdf_hash' => $processed['hash'],
            'can_generate_abstract' => $canGenerateAbstract,
            'scan' => $processed['scan'],
        ]);
    }

    // Only the owner, a co-author, their adviser, an admin, or anyone (once
    // the research is public + approved) may view/download a research
    // file. The rule itself now lives in ResearchAccess — the AI chatbot's
    // retrieval query enforces the identical rule in SQL
    // (ResearchAccess::sqlPredicate()), so this and the chatbot can never
    // drift apart into two different definitions of "who may read this".
    private function authorizeFileAccess(UserFile $file): void
    {
        abort_unless(
            ResearchAccess::allowsRepository(Auth::user(), $file),
            403
        );
    }

    // view function

    public function view($id)
    {
        $file = UserFile::findOrFail($id);

        $this->authorizeFileAccess($file);

        $file->increment('views');

        // Storage::disk(...)->path() only works for local-filesystem disks
        // and throws on S3/remote ones — response() (inline Content-
        // Disposition) is Laravel's disk-agnostic equivalent, streaming
        // the file regardless of which disk config('research.storage_disk')
        // points at.
        return Storage::disk($this->disk())->response($file->file_path);
    }

    // download function
    public function download($id)
    {
        $file = UserFile::findOrFail($id);

        $this->authorizeFileAccess($file);

        $file->increment('downloads');

        return Storage::disk($this->disk())->download(
            $file->file_path,
            basename($file->file_path)
        );
    }

    // update research
    public function update(UpdateResearchRequest $request, $id)
    {

        $file = UserFile::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (! $file->canResubmit()) {
            return back()
                ->withErrors([
                    'limit' => 'You have reached the maximum of '
                        .$file::MAX_RESUBMITS
                        .' resubmissions for this research.',
                ])
                ->with('_popover_target', 'update-sub-'.$file->id)
                ->withInput();
        }

        $adviser = User::findOrFail($request->adviser_id);

        $file->title = $request->title;
        $file->adviser = $adviser->name;
        $file->adviser_id = $adviser->id;
        $file->research_type = $request->research_type;
        $file->publication_year = $request->publication_year;
        $file->desc = $request->desc;

        // Keyword provenance: if the PDF isn't being replaced, fall back to
        // whatever this file's pivot already recorded as 'extracted' rather
        // than losing that distinction.
        $extractedKeywords = $file->keywords()
            ->wherePivot('source', 'extracted')
            ->pluck('display_name')
            ->all();

        // Replace PDF only if a new file was selected. Storage is
        // content-addressed (see store()), so the old file is simply left
        // alone rather than deleted — it may still be referenced by another
        // submission with identical content, and re-extraction/re-indexing
        // naturally re-runs against the new hash.
        if ($request->hasFile('file')) {
            try {
                $processed = $this->pdfValidation->process($request->file('file'));
            } catch (PdfProcessingException $e) {
                return back()->withErrors(['file' => $e->getMessage()])->withInput();
            }

            $hash = $processed['hash'];
            $storagePath = "research_files/{$hash}.pdf";

            // Content-addressed hash comparison: a re-upload of the
            // byte-identical PDF (same hash as what's already stored) used
            // to still trigger a full re-embed below via hasFile() alone —
            // wasted OpenAI calls for a document whose content didn't
            // change at all.
            $documentChanged = $hash !== $file->pdf_hash;

            if (! Storage::disk($this->disk())->exists($storagePath)) {
                Storage::disk($this->disk())->putFileAs('research_files', $request->file('file'), "{$hash}.pdf");
            }

            $detected = $this->sectionDetector->detectCached($processed['hash'], $processed['pages'], $processed['full_text']);

            $file->file_path = $storagePath;
            $file->pdf_hash = $hash;
            $file->pdf_page_count = $processed['page_count'];
            $file->extracted_abstract = $detected['abstract']['text'];
            $file->extracted_sections = $detected['sections'];
            $extractedKeywords = $detected['keywords'];

            // Only mark the index stale when the document's content
            // actually changed — see $documentChanged above.
            if ($documentChanged) {
                $file->search_index_status = 'not_started';
            }
        }

        $file->abstract_source = $this->abstractSource($file->pdf_hash, $file->desc, $file->extracted_abstract);

        // Send back for approval
        $file->status = 'pending';

        $file->save();
        $file->increment('resubmit_count');

        $file->sdgs()->sync($request->sdg);
        $this->syncAuthors($file, $request->input('researchers', []));

        $this->syncKeywords($file, $request->input('keywords', []), $request->input('keyword_sources', []), $extractedKeywords);
        $this->applyAiAuditFields($file, $request);

        // Re-index if the document itself changed (search_index_status was
        // just set to 'not_started' above), or if it was never successfully
        // indexed (gives a prior failure another chance, since there's no
        // separate "retry indexing" action yet). Deliberately not keyed off
        // hasFile('file') alone — see $documentChanged above.
        if (in_array($file->search_index_status, ['not_started', 'failed'], true)) {
            $this->dispatchIndexing($file->id);
        }

        self::forgetAdminDashboardCache($file, $adviser->id);
        $adviser->notify(new ResearchSubmitted($file));

        return redirect()
            ->back()
            ->with('success', 'Research submission updated successfully.');
    }
}
