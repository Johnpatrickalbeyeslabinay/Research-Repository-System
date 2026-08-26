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
            'login' => 'required',
            'password' => 'required',
        ]);

        $login = $request->login; // Check kung email o user ID ang inilagay
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'user_id';

        if (
            Auth::attempt([
                $field => $login,
                'password' => $request->password,
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
            'login' => 'Invalid User ID or Password.',
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
