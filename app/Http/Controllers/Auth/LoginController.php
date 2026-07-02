<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Show login form
    public function showLoginForm()
    {
        return view('auth.login'); 
    }

    // Handle login
    public function login(Request $request)
    {
        $request->validate([
            'role' => 'required|in:student,instructor',
            'id_number' => 'required',
            'password' => 'required|string',
        ]);

        // Determine which field to use based on role
        $idField = $request->role === 'student' ? 'stud_id' : 'instructor_id';

        $credentials = [
            $idField => $request->id_number,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();
            if ($user->role === 'instructor') {
                return redirect()->intended('/instructor_dashboard');
            } else {
                return redirect()->intended('/student_dashboard');
            }
        }

        return back()->withErrors([
            'id_number' => 'The provided credentials do not match our records.',
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}