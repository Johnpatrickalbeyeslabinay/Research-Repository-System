<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'password' => 'required',
            'role' => 'required',
        ]);

        if (
            Auth::attempt([
                'user_id' => $request->user_id,
                'password' => $request->password,
                'role' => $request->role,
            ])
        ) {

            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->role == 'student') {
                return redirect()->route('student');
            }

            if ($user->role == 'instructor') {
                return redirect()->route('instructor');
            }

            if ($user->role == 'admin') {
                return redirect()->route('admin');
            }
        }

        return back()->withErrors([
            'user_id' => 'Invalid User ID, Password or Role.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}