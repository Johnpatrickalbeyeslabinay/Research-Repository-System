<?php

namespace App\Http\Controllers;

use App\Models\UserFile;

class PublicController extends Controller
{
    public function index()
    {
        $studFiles = UserFile::where('visibility', 'public')
            ->where('status', 'approved')
            ->get();

        return view('public', compact('studFiles'));
    }
}
