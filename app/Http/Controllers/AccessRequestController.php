<?php

namespace App\Http\Controllers;

use App\Mail\AccessRequestStatusMail;
use App\Models\AccessRequest;
use App\Models\UserFile;
use App\Notifications\AccessRequestReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AccessRequestController extends Controller
{
    /**
     * Store a new research access request.
     */
    public function store(Request $request, UserFile $file)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'reason_access' => [
                'required',
                'in:Research Gap Identification,Related Study Comparison,Research Problem Identification,General Academic Learning',
            ],
        ]);

        $accessRequest = AccessRequest::create([
            'stud_file_id' => $file->id,
            'email' => $validated['email'],
            'reason_access' => $validated['reason_access'],
            'status' => 'pending',
        ]);

        // Remember the requester in this browser session
        // so Public Viewing can show "Access Request Pending".
        session([
            'research_access_email_'.$file->id => $validated['email'],
        ]);

        $uploader = $file->user;

        if ($uploader) {
            $uploader->notify(
                new AccessRequestReceived($accessRequest)
            );
        }

        return redirect()
            ->back()
            ->with(
                'success',
                'Your access request has been submitted successfully.'
            );
    }

    // ---------- Dashboard approve/reject ----------

    public function approve(Request $request, AccessRequest $accessRequest)
    {
        if ($accessRequest->status !== 'pending') {
            return redirect()->back()->with(
                'error',
                'This request has already been processed.'
            );
        }

        $accessRequest->update([
            'status' => 'approved',
        ]);

        $ownerName = auth()->user()?->name
            ?? $accessRequest->file?->user?->name
            ?? 'the owner';

        try {
            Mail::to($accessRequest->email)->send(
                new AccessRequestStatusMail($accessRequest, $ownerName)
            );

            return redirect()->back()->with(
                'success',
                'Access request approved successfully.'
            );
        } catch (\Throwable $e) {
            Log::error(
                'Access request approval email failed: '.$e->getMessage()
            );

            return redirect()->back()->with(
                'warning',
                'Access request approved, but the email could not be sent.'
            );
        }
    }

    public function reject(Request $request, AccessRequest $accessRequest)
    {
        if ($accessRequest->status !== 'pending') {
            return redirect()->back()->with(
                'error',
                'This request has already been processed.'
            );
        }

        $accessRequest->update([
            'status' => 'rejected',
        ]);

        $ownerName = auth()->user()?->name
            ?? $accessRequest->file?->user?->name
            ?? 'the owner';

        try {
            Mail::to($accessRequest->email)->send(
                new AccessRequestStatusMail($accessRequest, $ownerName)
            );

            return redirect()->back()->with(
                'success',
                'Access request rejected successfully.'
            );
        } catch (\Throwable $e) {
            Log::error(
                'Access request rejection email failed: '.$e->getMessage()
            );

            return redirect()->back()->with(
                'warning',
                'Access request rejected, but the email could not be sent.'
            );
        }
    }

    public function approveFromEmail(AccessRequest $accessRequest)
    {
        $message = $this->processRequest($accessRequest, 'approved');

        return view('access-request.result', [
            'message' => $message,
        ]);
    }

    public function rejectFromEmail(AccessRequest $accessRequest)
    {
        $message = $this->processRequest($accessRequest, 'rejected');

        return view('access-request.result', [
            'message' => $message,
        ]);
    }

    public function approvedAccess(AccessRequest $accessRequest)
    {
        abort_unless(
            $accessRequest->status === 'approved',
            403
        );

        $file = $accessRequest->file;

        abort_unless($file, 404);

        $file->load([
            'authors',
            'keywords',
            'sdgs',
            'adviserUser',
        ]);

        return view('access-request.approved', [
            'accessRequest' => $accessRequest,
            'file' => $file,
        ]);
    }

    public function approvedPdf(AccessRequest $accessRequest)
    {
        abort_unless(
            $accessRequest->status === 'approved',
            403
        );

        $file = $accessRequest->file;

        abort_unless($file, 404);

        return \Storage::disk(
            config('research.storage_disk', 'local')
        )->response($file->file_path);
    }

    // ---------- Shared logic ----------

    private function processRequest(AccessRequest $accessRequest, string $status): string
    {
        if ($accessRequest->status !== 'pending') {
            return 'This request was already processed.';
        }

        $accessRequest->update(['status' => $status]);

        // auth() is empty when the owner clicks from email, so fall back to the file owner
        $ownerName = auth()->user()?->name
            ?? $accessRequest->file?->user?->name
            ?? 'the owner';

        try {
            Mail::to($accessRequest->email)->send(
                new AccessRequestStatusMail($accessRequest, $ownerName)
            );

            return "Request {$status} and the requester was notified by email.";
        } catch (\Throwable $e) {
            Log::error('Access request email failed: '.$e->getMessage());

            return "Request {$status}, but the email could not be sent.";
        }
    }
}
