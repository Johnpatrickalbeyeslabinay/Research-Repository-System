<?php

namespace App\Http\Controllers;

use App\Exceptions\PdfProcessingException;
use App\Exports\AdminReportExport;
use App\Imports\StudentImport;
use App\Jobs\ProcessResearchDocument;
use App\Mail\repositoryMail;
use App\Models\Keyword;
use App\Models\SearchLog;
use App\Models\User;
use App\Models\UserFile;
use App\Notifications\AccountCreated;
use App\Notifications\ResearchReviewedByAdmin;
use App\Services\Pdf\PdfValidationService;
use App\Services\Pdf\SectionDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    // Short-TTL cache for the dashboard's stat counts + chart data — see
    // index() for why (Supabase is remote; the win is cutting round trips,
    // not raw cache-backend speed). Explicitly busted by approve()/reject()
    // and StudentController's submit/update, so admins never wait out the
    // TTL to see their own action reflected.
    private const DASHBOARD_CACHE_TTL = 60;

    public function index()
    {
        $view = request('view', 'dashboard');

        // Role filter
        $role = request('role');
        $search = request('search');

        // Each tab only loads what it renders: the dashboard never showed
        // the user list, and the Manage Users tab never showed the research
        // queue, but both were fetched on every visit — including every
        // user in the system, unpaginated.
        $users = null;
        $studFiles = null;
        $statusFilter = request('status', 'admin_pending');

        if ($view === 'user') {
            $users = User::query();

            if ($role) {
                $users->where('role', $role);
            }

            if ($search) {
                $users->where(fn ($q) => $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('user_id', 'ilike', "%{$search}%"));
            }

            // Deliberately not cached — search/role-filter results must stay live.
            $users = $users->orderBy('name')->paginate(25)->withQueryString();
        } else {
            // Was UserFile::where('status', 'admin_pending')->get() — no eager
            // loading (meta.blade.php would N+1 across the list), no
            // ordering, no pagination, and admin_pending only, which made
            // the already-approved/already-rejected badge branches in
            // admin.blade.php unreachable: there was no way to look at a
            // past decision again. ?status= defaults to the original
            // behavior; 'all' or a specific status opens up the rest.
            $studFilesQuery = UserFile::with(['authors', 'keywords', 'sdgs'])->latest();

            if ($statusFilter !== 'all') {
                $studFilesQuery->where('status', $statusFilter);
            }

            // Year range filter
            $yearFrom = request('year_from');
            $yearTo = request('year_to');

            if ($yearFrom) {
                $studFilesQuery->where('publication_year', '>=', $yearFrom);
            }

            if ($yearTo) {
                $studFilesQuery->where('publication_year', '<=', $yearTo);
            }

            $studFiles = $studFilesQuery
                ->paginate(10)
                ->withQueryString();

        }

        // System-wide totals for the stat cards — one GROUP BY instead of
        // counting a fully loaded (and search-filtered) user collection.
        $userCounts = User::selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role')
            ->map(fn ($n) => (int) $n);

        // $year = request('year', date('Y'));
        $year = (int) request('year', date('Y'));
        $period = request('period', 'annually');

        $stats = $view === 'user' ? [] : Cache::remember(
            "admin_dashboard_stats:{$year}",
            self::DASHBOARD_CACHE_TTL,
            fn () => $this->computeDashboardStats($year)
        );

        $reportData = $this->getReportData($period, $year);

        $unreadNotificationsCount = Auth::user()->unreadNotifications()->count();

        $limitReachedFiles = UserFile::with('user')
            ->where('resubmit_count', '>=', UserFile::MAX_RESUBMITS)
            ->latest('updated_at')
            ->get();

        $backups = \App\Models\Backup::latest()->get();
        $lastBackup = \App\Models\Backup::latest('created_at')->first();
        $totalBackups = \App\Models\Backup::count();

        return view('admin', array_merge(compact(
            'view',
            'users',
            'userCounts',
            'role',
            'search',
            'studFiles',
            'statusFilter',
            'year',
            'period',
            'reportData',
            'unreadNotificationsCount',
            'limitReachedFiles',
            'backups',
            'lastBackup',
            'totalBackups'
        ), $stats));
    }

    public function __construct(
        private PdfValidationService $pdfValidation,
        private SectionDetector $sectionDetector,
    ) {}

    /**
     * All the dashboard's aggregate numbers, computed in ~10 queries total
     * instead of the ~50 this used to run — the four month-by-month chart
     * arrays previously fired one query per month each (48 queries alone);
     * now each is a single GROUP BY query, and the loop below just
     * reassembles the array from already-fetched data with zero DB calls.
     *
     * @return array{totalResearch:int, approvedCount:int, rejectedCount:int, pendingCount:int, uploadData:array, approvalData:array, viewData:array, downloadData:array}
     */
    private function computeDashboardStats(int $year): array
    {
        $totalResearch = UserFile::count();
        $approvedCount = UserFile::where('status', 'approved')->count();
        $rejectedCount = UserFile::where('status', 'rejected')->count();
        $pendingCount = UserFile::where('status', 'admin_pending')->count();

        $monthlyUploads = UserFile::whereYear('created_at', $year)
            ->selectRaw('EXTRACT(MONTH FROM created_at)::int as month, COUNT(*) as total')
            ->groupBy('month')->pluck('total', 'month');

        $monthlyApprovals = UserFile::where('status', 'approved')
            ->whereYear('created_at', $year)
            ->selectRaw('EXTRACT(MONTH FROM created_at)::int as month, COUNT(*) as total')
            ->groupBy('month')->pluck('total', 'month');

        $monthlyViews = UserFile::whereYear('created_at', $year)
            ->selectRaw('EXTRACT(MONTH FROM created_at)::int as month, SUM(views) as total')
            ->groupBy('month')->pluck('total', 'month');

        $monthlyDownloads = UserFile::whereYear('created_at', $year)
            ->selectRaw('EXTRACT(MONTH FROM created_at)::int as month, SUM(downloads) as total')
            ->groupBy('month')->pluck('total', 'month');

        $uploadData = [];
        $approvalData = [];
        $viewData = [];
        $downloadData = [];

        for ($month = 1; $month <= 12; $month++) {
            $uploadData[] = (int) ($monthlyUploads[$month] ?? 0);
            $approvalData[] = (int) ($monthlyApprovals[$month] ?? 0);
            $viewData[] = (int) ($monthlyViews[$month] ?? 0);
            $downloadData[] = (int) ($monthlyDownloads[$month] ?? 0);
        }

        return compact(
            'totalResearch',
            'approvedCount',
            'rejectedCount',
            'pendingCount',
            'uploadData',
            'approvalData',
            'viewData',
            'downloadData'
        );
    }

    /**
     * Busts the admin dashboard's stat cache for this record's submission
     * year, plus the repository's filter facets — an approval can add a
     * new publication year or research type to the public listing.
     */
    private static function forgetResearchCaches(UserFile $file): void
    {
        Cache::forget('admin_dashboard_stats:'.$file->created_at->year);
        RepositoryController::forgetFacets();
    }

    public function storeArchive(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:pdf',
                'max:10240',
            ],
            'title' => [
                'required',
                'string',
                'max:500',
            ],
            'author' => [
                'required',
                'string',
                'max:1000',
            ],
            'year_published' => [
                'required',
                'integer',
                'min:1900',
                'max:'.now()->year,
            ],
            'abstract' => [
                'required',
                'string',
            ],
            'keywords' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $uploadedFile = $request->file('file');

        /*
        |--------------------------------------------------------------------------
        | 1. Validate and process PDF
        |--------------------------------------------------------------------------
        */

        try {
            $processed = $this->pdfValidation->process($uploadedFile);
        } catch (PdfProcessingException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'file' => $e->getMessage(),
                ]);
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'file' => 'The PDF could not be processed. Please try another file.',
                ]);
        }

        $hash = $processed['hash'] ?? hash_file(
            'sha256',
            $uploadedFile->getRealPath()
        );

        /*
        |--------------------------------------------------------------------------
        | 2. Prevent duplicate PDF
        |--------------------------------------------------------------------------
        */

        $duplicate = UserFile::where('pdf_hash', $hash)->first();

        if ($duplicate) {
            return back()
                ->withInput()
                ->withErrors([
                    'file' => 'This research PDF already exists in the repository.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Store PDF in Supabase Storage
        |--------------------------------------------------------------------------
        |
        | Use the same disk configured by your existing repository upload flow.
        | If your project uses a specific disk helper, use that here.
        |
        */

        $disk = config('research.storage_disk', 'supabase');

        $storagePath = 'research_files/'.$hash.'.pdf';

        try {
            Storage::disk($disk)->putFileAs(
                'research_files',
                $uploadedFile,
                $hash.'.pdf'
            );
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'file' => 'The PDF could not be uploaded to storage.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Parse researchers
        |--------------------------------------------------------------------------
        */

        $authors = collect(explode(',', $request->author))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->unique(fn ($name) => mb_strtolower($name))
            ->values();

        /*
        |--------------------------------------------------------------------------
        | 5. Detect sections
        |--------------------------------------------------------------------------
        */

        $detectedSections = [];

        try {
            $detected = $this->sectionDetector->detectCached(
                $uploadedFile,
                $hash
            );

            $detectedSections = $detected['sections'] ?? [];
        } catch (\Throwable $e) {
            /*
             * Section detection should not prevent an otherwise valid
             * archived research from being uploaded.
             */
            report($e);
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Create research record
        |--------------------------------------------------------------------------
        */

        try {
            $fileRecord = UserFile::create([
                'user_id' => Auth::id(),

                'title' => $request->title,

                // Primary researcher / first researcher
                'name' => $authors->first(),

                'desc' => $request->abstract,

                'file_path' => $storagePath,

                'views' => 0,
                'downloads' => 0,

                // Archive research is immediately published.
                'status' => 'approved',
                'visibility' => 'public',

                'research_type' => 'Research',

                'publication_year' => $request->year_published,

                'pdf_hash' => $hash,

                'pdf_page_count' => $processed['page_count'] ?? null,

                'extracted_abstract' => $request->abstract,

                'extracted_sections' => $detectedSections,

                /*
                 * No AI keyword suggestion is used here.
                 * Keywords are manually entered by the administrator.
                 */
                'search_index_status' => 'not_started',
            ]);

            /*
            |--------------------------------------------------------------------------
            | 7. Save researchers
            |--------------------------------------------------------------------------
            */

            foreach ($authors as $position => $author) {
                $fileRecord->authors()->create([
                    'user_id' => null,
                    'author_name' => $author,
                    'role_label' => null,
                    'position' => $position + 1,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 8. Save manually entered keywords
            |--------------------------------------------------------------------------
            */

            $keywords = collect(explode(',', $request->keywords ?? ''))
                ->map(fn ($keyword) => trim($keyword))
                ->filter()
                ->unique(fn ($keyword) => mb_strtolower($keyword))
                ->values();

            foreach ($keywords as $keyword) {
                $keywordModel = Keyword::findOrCreateByDisplayName($keyword);

                $fileRecord->keywords()->syncWithoutDetaching([
                    $keywordModel->id => [
                        'source' => 'manual',
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 9. Process document for search / Ask AQUA
            |--------------------------------------------------------------------------
            */

            try {
                ProcessResearchDocument::dispatch($fileRecord->id);
            } catch (\Throwable $e) {
                /*
                 * Upload itself should remain successful even if the
                 * indexing job cannot be dispatched.
                 */
                report($e);
            }

            /*
            |--------------------------------------------------------------------------
            | 10. Clear repository/admin caches
            |--------------------------------------------------------------------------
            */

            self::forgetResearchCaches($fileRecord);

        } catch (\Throwable $e) {

            /*
             * If database saving fails after storage upload, remove the
             * uploaded PDF so we don't leave an orphaned storage file.
             */
            try {
                Storage::disk($disk)->delete($storagePath);
            } catch (\Throwable $storageException) {
                report($storageException);
            }

            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'file' => 'The research could not be saved. Please try again.',
                ]);
        }

        return redirect()
            ->route('admin', ['view' => 'upload'])
            ->with(
                'success',
                'Research uploaded and published successfully.'
            );
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|unique:users,user_id',
            'email' => 'required|email|unique:users,email',
            'role' => 'required',
            'name' => 'required',
            'year_level' => 'nullable|string',
        ]);

        $generatedPassword = Str::password(
            length: 8,
            letters: true,
            numbers: true,
            symbols: false
        );

        $user = User::create([
            'user_id' => $request->user_id,
            'email' => $request->email,
            'role' => $request->role,
            'name' => $request->name,
            'year_level' => $request->role === 'student'
                ? $request->year_level
                : null,
            'password' => Hash::make($generatedPassword),
        ]);
        $verificationUrl = URL::signedRoute(
            'verification.verify',
            [
                'user' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        Mail::to($request->email)->send(
            new repositoryMail(
                $request->name,
                $request->user_id,
                $request->email,
                $generatedPassword,
                $request->role,
                $verificationUrl
            )
        );

        $user->notify(new AccountCreated($user));

        return redirect()->back()->with([
            'success' => 'User created successfully!',
            'generated_password' => $generatedPassword,
            'generated_name' => $request->name,
            'generated_user_id' => $request->user_id,
        ]);
    }

    public function updateUser(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|unique:users,user_id,'.$id,
            'email' => 'required|email|unique:users,email,'.$id,
            'role' => 'required',
            'name' => 'required',
            'year_level' => 'nullable|string',
        ]);

        $user = User::findOrFail($id);

        $user->update([
            'user_id' => $request->user_id,
            'email' => $request->email,
            'role' => $request->role,
            'name' => $request->name,
            'year_level' => $request->role === 'student' ? $request->year_level : null,
        ]);

        return back()->with('success', 'User updated successfully.');
    }

    public function importStudents(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        Excel::import(
            new StudentImport,
            $request->file('file')
        );

        return redirect()->back()->with(
            'success',
            'Student accounts imported successfully. Login credentials have been sent to their email addresses.'
        );
    }

    // APPROBE BTN
    public function approve($id)
    {
        $file = UserFile::findOrFail($id);

        abort_if(
            $file->status !== 'admin_pending',
            409,
            'This research is not awaiting admin review.'
        );

        // Final approval by admin = publish publicly
        $file->status = 'approved';
        $file->visibility = 'public';

        $file->save();

        self::forgetResearchCaches($file);

        $file->user->notify(
            new ResearchReviewedByAdmin($file, 'approved')
        );

        return back()->with(
            'success',
            'Research approved and published successfully.'
        );
    }

    // REJECT BTN
    public function reject(Request $request, $id)
    {
        $file = UserFile::findOrFail($id);

        abort_if(
            $file->status !== 'admin_pending',
            409,
            'This research is not awaiting admin review.'
        );

        $request->validate([
            'admin_reject' => 'required|string|max:1000',
        ]);

        $file->status = 'rejected';

        // Admin comment
        $file->admin_reject = $request->admin_reject;

        $file->rejected_at = now();

        $file->save();

        self::forgetResearchCaches($file);

        $file->user->notify(
            new ResearchReviewedByAdmin($file, 'rejected')
        );

        return back()->with(
            'success',
            'Research rejected successfully.'
        );
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        $user->account_status =
            $user->account_status === 'active'
                ? 'deactivated'
                : 'active';

        $user->save();

        return back()->with(
            'success',
            'Account status updated successfully.'
        );

    }

    public function destroy($id)
    {
        $file = UserFile::findOrFail($id);

        $file->delete();

        self::forgetResearchCaches($file);

        return back()->with('success', 'Research deleted successfully.');
    }

    // AdminResearchController
    public function resetResubmits($id)
    {
        $file = UserFile::findOrFail($id); // use your actual model
        $file->update(['resubmit_count' => 0]);

        return back()->with('success', 'Resubmit limit has been reset.');
    }

    // graph

    private function getReportData(string $period, int $year): array
    {
        /*
        |--------------------------------------------------------------------------
        | ANNUALLY
        |--------------------------------------------------------------------------
        */

        if ($period === 'annually') {

            $research = UserFile::query()
                ->selectRaw('EXTRACT(YEAR FROM created_at)::int AS period')
                ->selectRaw('COUNT(*)::int AS total')
                ->groupByRaw('EXTRACT(YEAR FROM created_at)')
                ->orderBy('period')
                ->pluck('total', 'period');

            $students = User::query()
                ->where('role', 'student')
                ->selectRaw('EXTRACT(YEAR FROM created_at)::int AS period')
                ->selectRaw('COUNT(*)::int AS total')
                ->groupByRaw('EXTRACT(YEAR FROM created_at)')
                ->orderBy('period')
                ->pluck('total', 'period');

            $instructors = User::query()
                ->where('role', 'instructor')
                ->selectRaw('EXTRACT(YEAR FROM created_at)::int AS period')
                ->selectRaw('COUNT(*)::int AS total')
                ->groupByRaw('EXTRACT(YEAR FROM created_at)')
                ->orderBy('period')
                ->pluck('total', 'period');

            /*
            |--------------------------------------------------------------------------
            | Search keywords
            |--------------------------------------------------------------------------
            |
            | Annual mode:
            | each year becomes one frame in the bar race.
            |
            */

            $searchLogs = SearchLog::query()
                ->selectRaw('EXTRACT(YEAR FROM created_at)::int AS period')
                ->selectRaw('LOWER(TRIM(keyword)) AS keyword')
                ->selectRaw('COUNT(*)::int AS total')
                ->groupByRaw('EXTRACT(YEAR FROM created_at), LOWER(TRIM(keyword))')
                ->orderBy('period')
                ->get();

            $searchFrames = $searchLogs
                ->groupBy('period')
                ->map(function ($rows, $periodValue) {
                    return [
                        'label' => (string) $periodValue,
                        'data' => $rows
                            ->sortByDesc('total')
                            ->take(10)
                            ->values()
                            ->map(fn ($row) => [
                                'name' => $row->keyword,
                                'value' => (int) $row->total,
                            ])
                            ->all(),
                    ];
                })
                ->values()
                ->all();

            $years = collect()
                ->merge($research->keys())
                ->merge($students->keys())
                ->merge($instructors->keys())
                ->unique()
                ->sort()
                ->values();

            return [
                'research' => [
                    'labels' => $years
                        ->map(fn ($value) => (string) $value)
                        ->all(),

                    'values' => $years
                        ->map(fn ($value) => (int) ($research[$value] ?? 0))
                        ->all(),
                ],

                'users' => [
                    'labels' => $years
                        ->map(fn ($value) => (string) $value)
                        ->all(),

                    'students' => $years
                        ->map(fn ($value) => (int) ($students[$value] ?? 0))
                        ->all(),

                    'instructors' => $years
                        ->map(fn ($value) => (int) ($instructors[$value] ?? 0))
                        ->all(),
                ],

                'searchRace' => $searchFrames,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | QUARTERLY
        |--------------------------------------------------------------------------
        */

        if ($period === 'quarterly') {

            $research = UserFile::query()
                ->whereYear('created_at', $year)
                ->selectRaw('EXTRACT(QUARTER FROM created_at)::int AS period')
                ->selectRaw('COUNT(*)::int AS total')
                ->groupByRaw('EXTRACT(QUARTER FROM created_at)')
                ->orderBy('period')
                ->pluck('total', 'period');

            $students = User::query()
                ->where('role', 'student')
                ->whereYear('created_at', $year)
                ->selectRaw('EXTRACT(QUARTER FROM created_at)::int AS period')
                ->selectRaw('COUNT(*)::int AS total')
                ->groupByRaw('EXTRACT(QUARTER FROM created_at)')
                ->orderBy('period')
                ->pluck('total', 'period');

            $instructors = User::query()
                ->where('role', 'instructor')
                ->whereYear('created_at', $year)
                ->selectRaw('EXTRACT(QUARTER FROM created_at)::int AS period')
                ->selectRaw('COUNT(*)::int AS total')
                ->groupByRaw('EXTRACT(QUARTER FROM created_at)')
                ->orderBy('period')
                ->pluck('total', 'period');

            /*
            |--------------------------------------------------------------------------
            | Search keywords
            |--------------------------------------------------------------------------
            */

            $searchLogs = SearchLog::query()
                ->whereYear('created_at', $year)
                ->selectRaw('EXTRACT(QUARTER FROM created_at)::int AS period')
                ->selectRaw('LOWER(TRIM(keyword)) AS keyword')
                ->selectRaw('COUNT(*)::int AS total')
                ->groupByRaw('EXTRACT(QUARTER FROM created_at), LOWER(TRIM(keyword))')
                ->orderBy('period')
                ->get();

            $searchFrames = collect(range(1, 4))
                ->map(function ($quarter) use ($searchLogs) {

                    $rows = $searchLogs
                        ->where('period', $quarter)
                        ->sortByDesc('total')
                        ->take(10);

                    return [
                        'label' => 'Q'.$quarter,
                        'data' => $rows
                            ->values()
                            ->map(fn ($row) => [
                                'name' => $row->keyword,
                                'value' => (int) $row->total,
                            ])
                            ->all(),
                    ];
                })
                ->all();

            $labels = [
                'Jan, Feb, Mar',
                'Apr, May, Jun',
                'Jul, Aug, Sep',
                'Oct, Nov, Dec',
            ];

            $researchValues = [];
            $studentValues = [];
            $instructorValues = [];

            for ($i = 1; $i <= 4; $i++) {
                $researchValues[] = (int) ($research[$i] ?? 0);
                $studentValues[] = (int) ($students[$i] ?? 0);
                $instructorValues[] = (int) ($instructors[$i] ?? 0);
            }

            return [
                'research' => [
                    'labels' => $labels,
                    'values' => $researchValues,
                ],

                'users' => [
                    'labels' => $labels,
                    'students' => $studentValues,
                    'instructors' => $instructorValues,
                ],

                'searchRace' => $searchFrames,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | MONTHLY
        |--------------------------------------------------------------------------
        */

        if ($period === 'monthly') {

            $research = UserFile::query()
                ->whereYear('created_at', $year)
                ->selectRaw('EXTRACT(MONTH FROM created_at)::int AS period')
                ->selectRaw('COUNT(*)::int AS total')
                ->groupByRaw('EXTRACT(MONTH FROM created_at)')
                ->orderBy('period')
                ->pluck('total', 'period');

            $students = User::query()
                ->where('role', 'student')
                ->whereYear('created_at', $year)
                ->selectRaw('EXTRACT(MONTH FROM created_at)::int AS period')
                ->selectRaw('COUNT(*)::int AS total')
                ->groupByRaw('EXTRACT(MONTH FROM created_at)')
                ->orderBy('period')
                ->pluck('total', 'period');

            $instructors = User::query()
                ->where('role', 'instructor')
                ->whereYear('created_at', $year)
                ->selectRaw('EXTRACT(MONTH FROM created_at)::int AS period')
                ->selectRaw('COUNT(*)::int AS total')
                ->groupByRaw('EXTRACT(MONTH FROM created_at)')
                ->orderBy('period')
                ->pluck('total', 'period');

            /*
            |--------------------------------------------------------------------------
            | Search keywords
            |--------------------------------------------------------------------------
            */

            $searchLogs = SearchLog::query()
                ->whereYear('created_at', $year)
                ->selectRaw('EXTRACT(MONTH FROM created_at)::int AS period')
                ->selectRaw('LOWER(TRIM(keyword)) AS keyword')
                ->selectRaw('COUNT(*)::int AS total')
                ->groupByRaw('EXTRACT(MONTH FROM created_at), LOWER(TRIM(keyword))')
                ->orderBy('period')
                ->get();

            $searchFrames = collect(range(1, 12))
                ->map(function ($month) use ($searchLogs) {

                    $rows = $searchLogs
                        ->where('period', $month)
                        ->sortByDesc('total')
                        ->take(10);

                    return [
                        'label' => date('F', mktime(0, 0, 0, $month, 1)),
                        'data' => $rows
                            ->values()
                            ->map(fn ($row) => [
                                'name' => $row->keyword,
                                'value' => (int) $row->total,
                            ])
                            ->all(),
                    ];
                })
                ->all();

            $labels = [
                'Jan', 'Feb', 'Mar', 'Apr',
                'May', 'Jun', 'Jul', 'Aug',
                'Sep', 'Oct', 'Nov', 'Dec',
            ];

            $researchValues = [];
            $studentValues = [];
            $instructorValues = [];

            for ($i = 1; $i <= 12; $i++) {
                $researchValues[] = (int) ($research[$i] ?? 0);
                $studentValues[] = (int) ($students[$i] ?? 0);
                $instructorValues[] = (int) ($instructors[$i] ?? 0);
            }

            return [
                'research' => [
                    'labels' => $labels,
                    'values' => $researchValues,
                ],

                'users' => [
                    'labels' => $labels,
                    'students' => $studentValues,
                    'instructors' => $instructorValues,
                ],

                'searchRace' => $searchFrames,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | WEEKLY
        |--------------------------------------------------------------------------
        */

        if ($period === 'weekly') {

            $research = UserFile::query()
                ->whereYear('created_at', $year)
                ->selectRaw('EXTRACT(WEEK FROM created_at)::int AS period')
                ->selectRaw('COUNT(*)::int AS total')
                ->groupByRaw('EXTRACT(WEEK FROM created_at)')
                ->orderBy('period')
                ->pluck('total', 'period');

            $students = User::query()
                ->where('role', 'student')
                ->whereYear('created_at', $year)
                ->selectRaw('EXTRACT(WEEK FROM created_at)::int AS period')
                ->selectRaw('COUNT(*)::int AS total')
                ->groupByRaw('EXTRACT(WEEK FROM created_at)')
                ->orderBy('period')
                ->pluck('total', 'period');

            $instructors = User::query()
                ->where('role', 'instructor')
                ->whereYear('created_at', $year)
                ->selectRaw('EXTRACT(WEEK FROM created_at)::int AS period')
                ->selectRaw('COUNT(*)::int AS total')
                ->groupByRaw('EXTRACT(WEEK FROM created_at)')
                ->orderBy('period')
                ->pluck('total', 'period');

            /*
            |--------------------------------------------------------------------------
            | Search keywords
            |--------------------------------------------------------------------------
            */

            $searchLogs = SearchLog::query()
                ->whereYear('created_at', $year)
                ->selectRaw('EXTRACT(WEEK FROM created_at)::int AS period')
                ->selectRaw('LOWER(TRIM(keyword)) AS keyword')
                ->selectRaw('COUNT(*)::int AS total')
                ->groupByRaw('EXTRACT(WEEK FROM created_at), LOWER(TRIM(keyword))')
                ->orderBy('period')
                ->get();

            $searchFrames = collect(range(1, 53))
                ->map(function ($week) use ($searchLogs) {

                    $rows = $searchLogs
                        ->where('period', $week)
                        ->sortByDesc('total')
                        ->take(10);

                    return [
                        'label' => 'Week '.$week,
                        'data' => $rows
                            ->values()
                            ->map(fn ($row) => [
                                'name' => $row->keyword,
                                'value' => (int) $row->total,
                            ])
                            ->all(),
                    ];
                })
                ->all();

            $labels = [];
            $researchValues = [];
            $studentValues = [];
            $instructorValues = [];

            for ($i = 1; $i <= 52; $i++) {
                $labels[] = "Week {$i}";

                $researchValues[] = (int) ($research[$i] ?? 0);
                $studentValues[] = (int) ($students[$i] ?? 0);
                $instructorValues[] = (int) ($instructors[$i] ?? 0);
            }

            return [
                'research' => [
                    'labels' => $labels,
                    'values' => $researchValues,
                ],

                'users' => [
                    'labels' => $labels,
                    'students' => $studentValues,
                    'instructors' => $instructorValues,
                ],

                'searchRace' => $searchFrames,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | FALLBACK
        |--------------------------------------------------------------------------
        */

        return [
            'research' => [
                'labels' => [],
                'values' => [],
            ],

            'users' => [
                'labels' => [],
                'students' => [],
                'instructors' => [],
            ],

            'searchRace' => [],
        ];
    }

    // PRINT REPORT CONTROLLRR
    public function printReport()
    {
        $year = (int) request('year', now()->year);
        $period = request('period', 'annually');

        $reportData = $this->getReportData($period, $year);

        return view('admin.report-print', [
            'reportData' => $reportData,
            'year' => $year,
            'period' => $period,
        ]);
    }

    public function exportReport(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $period = $request->input('period', 'annually');

        $reportData = $this->getReportData($period, $year);

        return Excel::download(
            new AdminReportExport(
                $reportData,
                $period,
                $year
            ),
            "admin-report-{$year}-{$period}.xlsx"
        );
    }

    private function getMonthlyStatusData(int $year): array
    {
        $rows = UserFile::query()
            ->whereYear('created_at', $year)
            ->whereIn('status', ['approved', 'admin_pending', 'rejected'])
            ->selectRaw('EXTRACT(MONTH FROM created_at)::int AS month, status, COUNT(*)::int AS total')
            ->groupByRaw('EXTRACT(MONTH FROM created_at), status')
            ->get();

        $approved = $pending = $rejected = [];

        for ($m = 1; $m <= 12; $m++) {
            $forMonth = $rows->where('month', $m);

            $approved[] = (int) $forMonth->where('status', 'approved')->sum('total');
            $pending[] = (int) $forMonth->where('status', 'admin_pending')->sum('total');
            $rejected[] = (int) $forMonth->where('status', 'rejected')->sum('total');
        }

        $years = UserFile::query()
            ->selectRaw('DISTINCT EXTRACT(YEAR FROM created_at)::int AS y')
            ->orderByDesc('y')
            ->pluck('y')
            ->all();

        return [
            'year' => $year,
            'years' => $years ?: [now()->year],
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
        ];
    }

    public function monthlyStatus(Request $request)
    {
        return response()->json(
            $this->getMonthlyStatusData((int) $request->input('year', now()->year))
        );
    }
}
