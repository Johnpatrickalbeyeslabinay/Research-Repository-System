<?php

namespace App\Http\Controllers;

use App\Models\UserFile;

class RepositoryController extends Controller
{
    public function index()
    {
        $studFiles = UserFile::where('status', 'approved')->get();

        return view('repository', compact('studFiles'));
    }
}
