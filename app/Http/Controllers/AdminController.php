<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin');
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|unique:users,user_id',
            'role' => 'required',
            'name' => 'required',
        ]);

        $generatedPassword = Str::password(
            length: 8,
            letters: true,
            numbers: true,
            symbols: false
        );

        User::create([
            'user_id' => $request->user_id,
            'role' => $request->role,
            'name' => $request->name,
            'year_level' => $request->role === 'student'
                ? $request->year_level
                : null,
            'course' => 'BS Fisheries',
            'password' => Hash::make($generatedPassword),
        ]);

        return redirect()->back()->with([
            'success' => 'User created successfully!',
            'generated_password' => $generatedPassword,
            'generated_name' => $request->name,
            'generated_user_id' => $request->user_id,
        ]);
    }
}