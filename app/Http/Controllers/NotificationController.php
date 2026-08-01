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

        $type = $request->type;
        $query = Notification::forUser($userId);

        if ($type && in_array($type, ['success', 'error', 'warning', 'info'])) {
            $query->where('type', $type);
        }

        $notifications = $query->latest()->paginate(20);

        $successCount = Notification::forUser($userId)->where('type', 'success')->count();
        $errorCount = Notification::forUser($userId)->where('type', 'error')->count();
        $warningCount = Notification::forUser($userId)->where('type', 'warning')->count();
        $infoCount = Notification::forUser($userId)->where('type', 'info')->count();
        $unreadCount = $this->notificationService->getUnreadCount($userId);

        return view('Backend.notifications.index', compact(
            'notifications', 'successCount', 'errorCount', 'warningCount', 'infoCount', 'unreadCount'
        ));
    }

    public function markAsRead($id)
    {
        $this->notificationService->markAsRead($id);
        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request)
    {
        $this->notificationService->markAllAsRead(auth()->id());
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'All notifications marked as read.');
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => $this->notificationService->getUnreadCount(auth()->id()),
        ]);
    }

    public function deleteNotification($id)
    {
        Notification::where('id', $id)->where('user_id', auth()->id())->delete();
        return response()->json(['success' => true]);
    }

    public function clearAll(Request $request)
    {
        Notification::where('user_id', auth()->id())->delete();
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'All notifications cleared.');
    }
}
