<?php

namespace App\Http\Controllers;

use App\Models\Sdg;
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

        // Rejected submissions
        $rejectedFiles = UserFile::where('user_id', Auth::id())
            ->where('status', 'Rejected')
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

        // Get all SDGs
        $sdgs = Sdg::orderBy('number')->get();

        return view('student', compact(
            'view',
            'studFiles',
            'notifications',
            'rejectedFiles',
            'totalUploads',
            'pendingUploads',
            'approvedUploads',
            'rejectedUploads',
            'sdgs'
        ));
    }

    public function store(Request $request)
    {
        // Validate
        $request->validate([
            'title' => 'required',
            'adviser' => 'required',
            'desc' => 'required',
            'sdg' => 'required|array|min:1',
            'sdg.*' => 'exists:sdgs,id',
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
        $fileRecord = UserFile::create([
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
            'visibility' => 'private',
            'requested_visibility' => $request->visibility,
        ]);

        $fileRecord->sdgs()->sync($request->sdg);

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

    // update research
    public function update(Request $request, $id)
    {
        $file = UserFile::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'adviser' => 'required|string|max:255',
            'desc' => 'required|string',
            'sdg' => 'required|array|min:1',
            'sdg.*' => 'exists:sdgs,id',
            'visibility' => 'required|in:public,private',
            'file' => 'nullable|mimes:pdf|max:10240',
        ]);

        $file->title = $request->title;
        $file->adviser = $request->adviser;
        $file->desc = $request->desc;
        $file->visibility = $request->visibility;

        // Replace PDF only if a new file was selected
        if ($request->hasFile('file')) {

            // Delete old PDF
            if (
                $file->file_path &&
                Storage::disk('public')->exists($file->file_path)
            ) {
                Storage::disk('public')->delete($file->file_path);
            }

            // Store new PDF
            $path = $request->file('file')
                ->store('research_files', 'public');

            $file->file_path = $path;
        }

        // Send back for approval
        $file->status = 'pending';

        $file->save();

        $file->sdgs()->sync($request->sdg);

        return redirect()
            ->back()
            ->with('success', 'Research submission updated successfully.');

    }
}
