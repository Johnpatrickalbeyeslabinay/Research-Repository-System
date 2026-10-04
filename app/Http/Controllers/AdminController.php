<?php

namespace App\Http\Controllers;

use App\Imports\StudentImport;
use App\Mail\repositoryMail;
use App\Models\User;
use App\Models\UserFile;
use App\Notifications\AccountCreated;
use App\Notifications\ResearchReviewedByAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

            $studFiles = $studFilesQuery->paginate(10)->withQueryString();
        }

        // System-wide totals for the stat cards — one GROUP BY instead of
        // counting a fully loaded (and search-filtered) user collection.
        $userCounts = User::selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role')
            ->map(fn ($n) => (int) $n);

        $year = request('year', date('Y'));

        // Stat cards and charts live on the dashboard tab only.
        $stats = $view === 'user' ? [] : Cache::remember(
            "admin_dashboard_stats:{$year}",
            self::DASHBOARD_CACHE_TTL,
            fn () => $this->computeDashboardStats($year)
        );

        $unreadNotificationsCount = Auth::user()->unreadNotifications()->count();

        $limitReachedFiles = UserFile::with('user')
            ->where('resubmit_count', '>=', UserFile::MAX_RESUBMITS)
            ->latest('updated_at')
            ->get();

        return view('admin', array_merge(compact(
            'view',
            'users',
            'userCounts',
            'role',
            'search',
            'studFiles',
            'statusFilter',
            'year',
            'unreadNotificationsCount',
            'limitReachedFiles'
        ), $stats));
    }

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
}
