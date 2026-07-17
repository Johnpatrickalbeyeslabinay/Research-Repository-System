<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index()
    {
        $view = request('view', 'dashboard');

        $users = User::all();

        $student = User::where('role', 'student')->get();

        $instructor = User::where('role', 'instructor')->get();

        $admin = User::where('role', 'admin')->get();

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
            'student',
            'instructor',
            'admin',
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

    // APPROBE BTN
    public function approve($id)
    {
        $file = UserFile::findOrFail($id);

        $file->status = 'Approved';
        $file->save();

        return back()->with('success', 'Research approved successfully.');
    }

    // REJECT BTN
    public function reject($id)
    {
        $file = UserFile::findOrFail($id);

        $file->status = 'Rejected';
        $file->save();

        return back()->with('success', 'Research rejected successfully.');
    }
}
