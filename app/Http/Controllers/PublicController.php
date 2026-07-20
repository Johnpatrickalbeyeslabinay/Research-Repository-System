<?php

namespace App\Http\Controllers;

use App\Models\UserFile;

class PublicController extends Controller
{
    public function index()
    {
        $studFiles = UserFile::all();

        return view('public', compact('studFiles'));
    }
}
