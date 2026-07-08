<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function index()
    {
        $studFiles = UserFile::all();

        $notifications = UserFile::where('user_id', Auth::user()->user_id)
            ->latest()
            ->get();

        return view('student', compact('studFiles', 'notifications'));
    }

    public function store(Request $request)
    {
        // 1. Validate
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
            'are'
        ];

        $tags = preg_replace('/[^a-z0-9\s]/', '', strtolower($request->title));
        $tags = explode(' ', $tags);
        $tags = array_filter($tags, fn($word) => !empty($word) && !in_array($word, $stopWords));
        $tags = array_unique($tags);

        $tags = implode(',', $tags);


        // 3. Upload file  
        $file = $request->file('file');

        $originalName = $file->getClientOriginalName();

        $file->storeAs('research_files', $originalName, 'public');

        $filePath = 'research_files/' . $originalName;


        // 4. Save to database
        UserFile::create([
            'user_id' => Auth::user()->user_id,
            'title' => $request->title,
            'name' => Auth::user()->name,
            'adviser' => $request->adviser,
            'desc' => $request->desc,
            'file_path' => $filePath,
            'auto_tag' => $tags,
        ]);

        return redirect()->route('student')->with('success', 'Research submitted successfully!');
    }


    //download function
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