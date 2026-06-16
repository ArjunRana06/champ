<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $userId = auth()->id();

        if ($request->ajax()) {
            $notifications = $this->notificationService->getRecent($userId);
            $unreadCount = $this->notificationService->getUnreadCount($userId);
            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ]);
        }

        $notifications = Notification::forUser($userId)->latest()->paginate(20);
        return view('Backend.notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $this->notificationService->markAsRead($id);
        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        $this->notificationService->markAllAsRead(auth()->id());
        return response()->json(['success' => true]);
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => $this->notificationService->getUnreadCount(auth()->id()),
        ]);
    }
}
