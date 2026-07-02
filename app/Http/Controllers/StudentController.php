<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserFile;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function index()
    {
        $studFiles = UserFile::all();

        return view('student', compact('studFiles'));
    }

    public function store(Request $request)
    {
        // 1. Validate
        $request->validate([
            'title' => 'required',
            'course' => 'required',
            'adviser' => 'required',
            'desc' => 'required',
            'file' => 'required|mimes:pdf,doc,docx|max:10240',
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

        $tags = preg_replace('/[^a-z0-9\s]/', '', $title);
        $tags = explode(' ', $tags);
        $tags = array_filter($tags, fn($word) => !empty($word) && !in_array($word, $stopWords));
        $tags = array_unique($tags);

        // 3. Upload file  <-- DITO ILALAGAY ANG STEP 2
        $file = $request->file('file');

        $originalName = $file->getClientOriginalName();

        $file->storeAs('research_files', $originalName, 'public');

        $filePath = 'research_files/' . $originalName;


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

        return redirect()->route('student')->with('success', 'Research submitted successfully!');
    }
}