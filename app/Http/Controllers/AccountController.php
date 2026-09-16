<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Role-agnostic authenticated-account actions — currently just password
 * change, used identically by the "Manage Account" modal on the student,
 * instructor, and admin dashboards.
 */
class AccountController extends Controller
{
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        // User::$password isn't cast to 'hashed' (see app/Models/User.php),
        // matching the rest of the codebase — Hash::check/Hash::make stay
        // manual, same as AdminController::store()'s generated-password flow.
        if (! Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'The current password you entered is incorrect.']);
        }

        Auth::user()->update(['password' => Hash::make($request->new_password)]);

        return back()->with('success', 'Password updated successfully.');
    }
}
