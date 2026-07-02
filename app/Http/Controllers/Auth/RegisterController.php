<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    // Show registration form
    public function showRegisterForm()
    {
        return view('auth.register'); 
    }

    // Handle registration
    public function register(Request $request)
    {
        // Grab all form data
        $data = $request->all();

        // Common validation
        $rules = [
            'role' => 'required|in:student,instructor',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ];

        if($data['role'] === 'student') {
            $rules['stud_id'] = 'required|unique:users,stud_id';
            $rules['year_level'] = 'required|in:1,2,3';
            $rules['program'] = 'required|string';
        } else {
            $rules['instructor_id'] = 'required|unique:users,instructor_id';
        }

        // Validate input
        $validated = $request->validate($rules);

        // Create User
            $user = User::create([
                'role' => $data['role'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'password' => Hash::make($data['password']),
                'stud_id' => $data['role'] === 'student' ? $data['stud_id'] : null,
                'instructor_id' => $data['role'] === 'instructor' ? $data['instructor_id'] : null,
                'year_level' => $data['role'] === 'student' ? $data['year_level'] : null,
                'program' => $data['role'] === 'student' ? $data['program'] : null,
            ]);

        $message = $data['role'] === 'student'
            ? 'Student registered successfully!'
            : 'Instructor registered successfully!';

        return redirect()->back()->with('success', $message);
    }
}