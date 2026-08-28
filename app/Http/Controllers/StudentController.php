<?php

namespace App\Http\Controllers;

use App\Models\UserFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function index()
    {
        $view = request('view', 'dashboard');

        $studFiles = UserFile::where('user_id', Auth::id())->get();

        $notifications = UserFile::where('user_id', Auth::id())
            ->latest()
            ->get();

        $totalUploads = UserFile::where('user_id', Auth::id())->count();

        $pendingUploads = UserFile::where('user_id', Auth::id())
            ->where('status', 'Pending')
            ->count();

        $approvedUploads = UserFile::where('user_id', Auth::id())
            ->where('status', 'Approved')
            ->count();

        $rejectedUploads = UserFile::where('user_id', Auth::id())
            ->where('status', 'Rejected')
            ->count();

        return view('student', compact(
            'view',
            'studFiles',
            'notifications',
            'totalUploads',
            'pendingUploads',
            'approvedUploads',
            'rejectedUploads'
        ));
    }

    public function store(Request $request)
    {
        // Validate
        $request->validate([
            'title' => 'required',
            'adviser' => 'required',
            'desc' => 'required',
            'file' => 'required|mimes:pdf|max:10240',
        ], [
            'file.required' => 'Please upload a PDF file.',
            'file.mimes' => 'Only PDF files are allowed. DOC and DOCX files are not accepted.',
            'file.max' => 'The PDF file must not exceed 10 MB.',
        ]);

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

        // Upload file
        $file = $request->file('file');

        $originalName = $file->getClientOriginalName();

        $file->storeAs('research_files', $originalName, 'public');

        $filePath = 'research_files/'.$originalName;

        // Save to database
        UserFile::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'name' => Auth::user()->name,
            'adviser' => $request->adviser,
            'desc' => $request->desc,
            'file_path' => $filePath,
            'auto_tag' => $tags,
            'views' => 0,
            'downloads' => 0,
            'status' => 'pending',
            'visibility' => $request->visibility,
        ]);

        return redirect()->route('student')->with('success', 'Research submitted successfully!');
    }

    // view function

    public function view($id)
    {
        $file = UserFile::findOrFail($id);

        $file->increment('views');

        return response()->file(
            storage_path('app/public/'.$file->file_path)
        );
    }

    // download function
    public function download($id)
    {
        $file = UserFile::findOrFail($id);

        $file->increment('downloads');

        return Storage::disk('public')->download(
            $file->file_path,
            basename($file->file_path)
        );
    }
}
