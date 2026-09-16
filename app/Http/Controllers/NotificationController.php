<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

/**
 * Role-agnostic notifications page — one controller/view for all three
 * roles (student/instructor/admin), each with their own bell icon linking
 * here. Uses Laravel's built-in database notifications (Auth::user() has
 * the Notifiable trait) rather than a custom notifications concept.
 */
class NotificationController extends Controller
{
    public function index()
    {
        // Not paginated — matches every other listing in this app
        // (UserFile::get(), not paginate()); 50 most recent is enough for
        // a first working version, and avoids pulling in Laravel's default
        // Tailwind-styled pagination view into an otherwise Bootstrap app.
        $notifications = Auth::user()->notifications()->latest()->limit(50)->get();
        $unreadNotificationsCount = Auth::user()->unreadNotifications()->count();

        return view('notifications', compact('notifications', 'unreadNotificationsCount'));
    }

    public function markRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back();
    }

    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back();
    }
}
