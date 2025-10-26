<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CourseEnrolled;
use App\Notifications\NewMessage;
use App\Notifications\CourseCompleted;
use App\Notifications\PaymentReceived;
use App\Notifications\CoursePublished;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->paginate(20);
        
        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        
        $notification->markAsRead();
        
        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();
        
        return response()->json(['success' => true]);
    }

    public function delete($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        
        $notification->delete();
        
        return response()->json(['success' => true]);
    }

    public function deleteAll()
    {
        $user = Auth::user();
        $user->notifications()->delete();
        
        return response()->json(['success' => true]);
    }

    public function getUnreadCount()
    {
        $user = Auth::user();
        $count = $user->unreadNotifications->count();
        
        return response()->json(['count' => $count]);
    }

    public function getRecent()
    {
        $user = Auth::user();
        $notifications = $user->notifications()
            ->latest()
            ->limit(5)
            ->get();
        
        return response()->json($notifications);
    }
}
