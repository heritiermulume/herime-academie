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
        
        if (request()->routeIs('instructor.notifications')) {
            return view('instructors.admin.notifications', compact('notifications'));
        }

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        
        $notification->markAsRead();
        
        $unreadCount = $user->unreadNotifications()->count();
        $recent = $this->formatNotifications(
            $user->notifications()
                ->where('notifiable_id', $user->id)
                ->latest()
                ->limit(5)
                ->get()
        );

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'recent' => $recent,
        ]);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();
        
        $recent = $this->formatNotifications($user->notifications()->latest()->limit(5)->get());

        return response()->json([
            'success' => true,
            'unread_count' => 0,
            'recent' => $recent,
        ]);
    }

    public function delete($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        
        $notification->delete();
        
        $unreadCount = $user->unreadNotifications()->count();
        $recent = $this->formatNotifications(
            $user->notifications()
                ->where('notifiable_id', $user->id)
                ->latest()
                ->limit(5)
                ->get()
        );

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'recent' => $recent,
        ]);
    }

    public function deleteAll()
    {
        $user = Auth::user();
        $user->notifications()->delete();
        
        return response()->json([
            'success' => true,
            'unread_count' => 0,
            'recent' => [],
        ]);
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
        $notifications = $this->formatNotifications(
            $user->notifications()
                ->where('notifiable_id', $user->id)
                ->latest()
                ->limit(5)
                ->get()
        );
        
        return response()->json($notifications);
    }

    protected function formatNotifications($notifications)
    {
        return $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'data' => array_merge($notification->data ?? [], [
                    'created_at_formatted' => $notification->created_at?->diffForHumans(),
                ]),
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
            ];
        });
    }
}
