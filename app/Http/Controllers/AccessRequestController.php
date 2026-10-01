<?php

namespace App\Http\Controllers;

use App\Models\AccessRequest;
use App\Models\UserFile;
use App\Notifications\AccessRequestReceived;
use Illuminate\Http\Request;

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

        $uploader = $file->user;

        if ($uploader) {
            $uploader->notify(
                new AccessRequestReceived($accessRequest)
            );
        }

        return redirect()->back()->with(
            'success',
            'Your access request has been submitted successfully.'
        );
    }
}
