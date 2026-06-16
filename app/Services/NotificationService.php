<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public function create(int $userId, string $type, string $title, ?string $body = null, ?string $link = null): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'link' => $link,
        ]);
    }

    public function markAsRead(int $notificationId): void
    {
        Notification::where('id', $notificationId)
            ->where('user_id', auth()->id())
            ->update(['is_read' => true]);
    }

    public function markAllAsRead(int $userId): void
    {
        Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    public function getUnreadCount(int $userId): int
    {
        return Notification::forUser($userId)->unread()->count();
    }

    public function getRecent(int $userId, int $limit = 10)
    {
        return Notification::forUser($userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function notifyDocumentProcessed(int $userId, string $docName, int $docId): void
    {
        $this->create(
            $userId,
            'doc_ready',
            'Document processed',
            "Your document \"{$docName}\" has been fully processed and indexed.",
            route('documents.index')
        );
    }

    public function notifyQuizGenerated(int $userId, string $type, int $count): void
    {
        $routeMap = [
            'MCQ' => 'mcqs.index',
            'True/False' => 'true-false.index',
            'Short Answer' => 'short-answers.index',
            'Fill-in-the-Blank' => 'fill-blanks.index',
            'Matching' => 'matching.index',
            'Flashcard' => 'flashcards.index',
        ];
        $link = isset($routeMap[$type]) ? route($routeMap[$type]) : null;

        $this->create(
            $userId,
            'quiz_reminder',
            "{$count} {$type} generated",
            "New {$type} questions are ready for practice.",
            $link
        );
    }

}
