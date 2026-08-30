<?php

namespace App\Http\Controllers;

use App\Mail\repositoryMail;
use App\Models\User;
use App\Models\UserFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index()
    {
        $view = request('view', 'dashboard');

        // Role filter
        $role = request('role');

        $users = User::query();

        if ($role) {
            $users->where('role', $role);
        }

        $users = $users->get();

        $studFiles = UserFile::all();

        $approvedCount = UserFile::where('status', 'approved')->count();
        $rejectedCount = UserFile::where('status', 'rejected')->count();
        $pendingCount = UserFile::where('status', 'pending')->count();

        $year = request('year', date('Y'));

        $uploadData = [];
        $approvalData = [];

        for ($month = 1; $month <= 12; $month++) {

            $uploadData[] = UserFile::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();

            $approvalData[] = UserFile::where('status', 'approved')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();
        }

        $viewData = [];
        $downloadData = [];

        for ($month = 1; $month <= 12; $month++) {

            $viewData[] = UserFile::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('views');

            $downloadData[] = UserFile::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('downloads');
        }

        return view('admin', compact(
            'view',
            'users',
            'role',
            'studFiles',
            'approvedCount',
            'rejectedCount',
            'pendingCount',
            'uploadData',
            'approvalData',
            'viewData',
            'downloadData',
            'year'
        ));
    }

    public function store(Request $request)
    {

        $request->validate([
            'user_id' => 'required',
            'email' => 'required|email',
            'role' => 'required',
            'name' => 'required',
            'year_level' => 'nullable|string',
        ]);

        $generatedPassword = Str::password(
            length: 8,
            letters: true,
            numbers: true,
            symbols: false
        );

        User::create([
            'user_id' => $request->user_id,
            'email' => $request->email,
            'role' => $request->role,
            'name' => $request->name,
            'year_level' => $request->role === 'student'
                ? $request->year_level
                : null,
            'password' => Hash::make($generatedPassword),
        ]);

        Mail::to($request->email)->send(
            new repositoryMail(
                $request->name,
                $request->user_id,
                $request->email,
                $generatedPassword,
                $request->role
            )
        );

        // return 'Email sent successfully!';

        return redirect()->back()->with([
            'success' => 'User created successfully!',
            'generated_password' => $generatedPassword,
            'generated_name' => $request->name,
            'generated_user_id' => $request->user_id,
        ]);

    }

    // APPROBE BTN
    public function approve($id)
    {
        $file = UserFile::findOrFail($id);

        $file->status = 'approved';

        if ($file->requested_visibility === 'public') {
            $file->visibility = 'public';
        }

        $file->save();

        return back()->with('success', 'Research approved successfully.');
    }

    // REJECT BTN
    public function reject($id)
    {
        $file = UserFile::findOrFail($id);

        $file->status = 'rejected';
        $file->save();

        return back()->with('success', 'Research rejected successfully.');
    }
}
