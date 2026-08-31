<?php

namespace App\Http\Controllers;

use App\Models\UserFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructorController extends Controller
{
    public function index()
    {
        $view = request('view', 'dashboard');

        // Pending research submissions only
        $pendingFiles = UserFile::where('adviser', Auth::user()->name)
            ->where('status', 'pending')
            ->latest()
            ->get();

        $studFiles = UserFile::where('adviser', Auth::user()->name)
            ->latest()
            ->get();

        $approvedCount = UserFile::where('adviser', Auth::user()->name)
            ->where('status', 'approved')
            ->count();

        $rejectedCount = UserFile::where('adviser', Auth::user()->name)
            ->where('status', 'rejected')
            ->count();

        $pendingCount = UserFile::where('adviser', Auth::user()->name)
            ->where('status', 'pending')
            ->count();

        return view('instructor', compact(
            'view',
            'studFiles',
            'pendingFiles',
            'approvedCount',
            'rejectedCount',
            'pendingCount'
        ));
    }

    public function store(Request $request)
    {
        // 1. Validate
        $request->validate([
            'title' => 'required',
            'course' => 'required',
            'adviser' => 'required',
            'desc' => 'required',
            'file' => 'required|mimes:pdf |max:10240',
        ]);

        // 2. Generate auto tags
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

        $tags = preg_replace('/[^a-z0-9\s]/', '', $title);
        $tags = explode(' ', $tags);
        $tags = array_filter($tags, fn ($word) => ! empty($word) && ! in_array($word, $stopWords));
        $tags = array_unique($tags);

        // 3. Upload file  <-- DITO ILALAGAY ANG STEP 2
        $file = $request->file('file');

        $originalName = $file->getClientOriginalName();

        $file->storeAs('research_files', $originalName, 'public');

        $filePath = 'research_files/'.$originalName;

        // 4. Save to database
        UserFile::create([
            'user_id' => Auth::user()->user_id,
            'title' => $request->title,
            'name' => $request->course,
            'adviser' => $request->adviser,
            'desc' => $request->desc,
            'file_path' => $filePath,
            'auto_tag' => implode(', ', $tags),
        ]);

        return redirect()->route('instructor')
            ->with('success', 'Research submitted successfully!');
    }

    // APPROVE RESEARCH
    public function approve($id)
    {
        $file = UserFile::findOrFail($id);

        $file->status = 'approved';

        if ($file->requested_visibility === 'public') {
            $file->visibility = 'public';
        }

        $file->save();

        return back()->with('success', 'Research approved successfully.');
    }

    // REJECT RESEARCH
    public function reject($id)
    {
        $file = UserFile::findOrFail($id);

        $file->status = 'rejected';

        $file->save();

        return back()->with('success', 'Research rejected successfully.');
    }
}
