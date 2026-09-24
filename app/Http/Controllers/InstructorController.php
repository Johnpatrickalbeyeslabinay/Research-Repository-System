<?php

namespace App\Http\Controllers;

use App\Models\UserFile;
use App\Notifications\ResearchFeedbackPosted;
use App\Notifications\ResearchReviewedByInstructor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class InstructorController extends Controller
{
    // Short-TTL cache for the dashboard stat counts — see AdminController
    // for the same pattern and why (Supabase is remote; cutting round
    // trips matters more here than cache-backend speed).
    private const DASHBOARD_CACHE_TTL = 60;

    public function index()
    {
        $view = request('view', 'dashboard');

        // Only the counts are cached now — this used to cache the full
        // pendingFiles/studFiles Eloquent collections for 60s under the
        // same key, with nothing busting it on a new submission
        // (StudentController::store() didn't touch this cache at all),
        // so a freshly submitted paper could take up to a minute to
        // appear in the adviser's own queue.
        $counts = Cache::remember(
            'instructor_dashboard_counts:'.Auth::id(),
            self::DASHBOARD_CACHE_TTL,
            fn () => [
                'approvedCount' => UserFile::where('adviser_id', Auth::id())->where('status', 'approved')->count(),
                'rejectedCount' => UserFile::where('adviser_id', Auth::id())->where('status', 'rejected')->count(),
                'pendingCount' => UserFile::where('adviser_id', Auth::id())->where('status', 'pending')->count(),
            ]
        );

        $approvedCount = $counts['approvedCount'];
        $rejectedCount = $counts['rejectedCount'];
        $pendingCount = $counts['pendingCount'];

        // meta.blade.php reads authors/keywords/sdgs on every row it's
        // given — eager-load them here (there was no eager loading at
        // all before) so the review tab doesn't N+1 across the list.
        $pendingFiles = UserFile::where('adviser_id', Auth::id())
            ->where('status', 'pending')
            ->with(['authors', 'keywords', 'sdgs'])
            ->latest()
            ->get();

        $studFiles = UserFile::where('adviser_id', Auth::id())
            ->with(['authors', 'keywords', 'sdgs',  'feedbacks.user'])
            ->latest()
            ->get();

        // Real in-app notifications (research submitted to this instructor,
        // etc.) — populates the dashboard's "Notification" tab, which was
        // previously a static stub with no data at all.
        $notifications = Auth::user()->notifications()->latest()->limit(20)->get();
        $unreadNotificationsCount = Auth::user()->unreadNotifications()->count();

        return view('instructor', compact(
            'view',
            'studFiles',
            'pendingFiles',
            'approvedCount',
            'rejectedCount',
            'pendingCount',
            'notifications',
            'unreadNotificationsCount'
        ));
    }

    private static function forgetDashboardCache(): void
    {
        Cache::forget('instructor_dashboard_counts:'.Auth::id());
    }

    // APPROVE RESEARCH
    public function approve($id)
    {
        $file = UserFile::where('id', $id)
            ->where('adviser_id', Auth::id())
            ->firstOrFail();

        // The UI only shows the Approve/Reject buttons while status is
        // 'pending' (instructor.blade.php), but nothing enforced that
        // server-side — a second, stale request (double-click, browser
        // back + resubmit) against an already-decided file silently
        // flipped its status again with no error.
        abort_if($file->status !== 'pending', 409, 'This research has already been reviewed.');

        // Instructor approved → send to Admin
        $file->status = 'admin_pending';

        $file->save();

        self::forgetDashboardCache();

        $file->user->notify(new ResearchReviewedByInstructor($file, 'approved'));

        return back()->with(
            'success',
            'Research approved and forwarded to Admin.'
        );
    }

    // REJECT RESEARCH
    public function reject($id)
    {
        $file = UserFile::where('id', $id)
            ->where('adviser_id', Auth::id())
            ->firstOrFail();

        abort_if($file->status !== 'pending', 409, 'This research has already been reviewed.');

        $file->status = 'rejected';

        $file->save();

        self::forgetDashboardCache();

        $file->user->notify(new ResearchReviewedByInstructor($file, 'rejected'));

        return back()->with(
            'success',
            'Research rejected successfully.'
        );
    }

    // POST FEEDBACK
    // public function feedback(Request $request, $id)
    // {
    // $request->validate([
    //   'feedback' => 'required|string|max:2000',
    // ]);

    // $file = UserFile::where('id', $id)
    //   ->where('adviser_id', Auth::id())
    // ->firstOrFail();

    // $file->instructor_feedback = $request->feedback;
    // $file->save();

    // Previously nothing told the student feedback existed at all —
    // they'd only ever see it if they happened to reopen the card.
    // $file->user->notify(new ResearchFeedbackPosted($file));

    // return back()->with('success', 'Feedback posted.');
    // }

    public function feedback(Request $request, $id)
    {
        $request->validate([
            'feedback' => 'required|string|max:2000',
        ]);

        $file = UserFile::where('id', $id)
            ->where('adviser_id', Auth::id())
            ->firstOrFail();

        // Kunin ang existing feedback history
        $feedbacks = [];

        if (! empty($file->instructor_feedback)) {
            $decoded = json_decode($file->instructor_feedback, true);

            if (is_array($decoded)) {
                $feedbacks = $decoded;
            } else {
                // Preserve old single feedback
                $feedbacks[] = [
                    'user_id' => $file->adviser_id,
                    'feedback' => $file->instructor_feedback,
                    'created_at' => $file->updated_at?->toDateTimeString(),
                ];
            }
        }

        // Add new feedback
        $feedbacks[] = [
            'user_id' => Auth::id(),
            'feedback' => $request->feedback,
            'created_at' => now()->toDateTimeString(),
        ];

        // Save entire history back to existing column
        $file->instructor_feedback = json_encode($feedbacks);
        $file->save();

        return back()->with('success', 'Feedback posted.');
    }
}
