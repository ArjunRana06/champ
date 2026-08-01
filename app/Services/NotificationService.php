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

    public function getRecent(int $userId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Notification::forUser($userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    // ─── Document notifications ───

    public function notifyDocumentProcessed(int $userId, string $docName, int $docId): void
    {
        $this->create(
            $userId,
            'success',
            'Document processed',
            "Your document \"{$docName}\" has been fully processed and indexed.",
            route('documents.index')
        );
    }

    public function notifyDocumentFailed(int $userId, string $docName): void
    {
        $this->create(
            $userId,
            'error',
            'Document processing failed',
            "Your document \"{$docName}\" could not be processed. Please try again.",
            route('documents.index')
        );
    }

    public function notifyDocumentUploaded(int $userId, string $docName): void
    {
        $this->create(
            $userId,
            'info',
            'Document uploaded',
            "Your document \"{$docName}\" has been uploaded and is being processed.",
            route('documents.index')
        );
    }

    public function notifyDocumentDeleted(int $userId, string $docName): void
    {
        $this->create(
            $userId,
            'warning',
            'Document deleted',
            "Your document \"{$docName}\" has been deleted.",
            route('documents.index')
        );
    }

    public function notifyDocumentRetried(int $userId, string $docName): void
    {
        $this->create(
            $userId,
            'info',
            'Document reprocessing',
            "Your document \"{$docName}\" has been re-queued for processing.",
            route('documents.index')
        );
    }

    public function notifyDocumentSummarized(int $userId, string $docName): void
    {
        $this->create(
            $userId,
            'success',
            'Document summarized',
            "Summary for \"{$docName}\" has been generated.",
            route('documents.index')
        );
    }

    // ─── Subject notifications ───

    public function notifySubjectCreated(int $userId, string $subjectName): void
    {
        $this->create(
            $userId,
            'success',
            'Subject created',
            "Subject \"{$subjectName}\" has been created.",
            route('subjects.index')
        );
    }

    public function notifySubjectUpdated(int $userId, string $subjectName): void
    {
        $this->create(
            $userId,
            'info',
            'Subject updated',
            "Subject \"{$subjectName}\" has been updated.",
            route('subjects.index')
        );
    }

    public function notifySubjectDeleted(int $userId, string $subjectName): void
    {
        $this->create(
            $userId,
            'warning',
            'Subject deleted',
            "Subject \"{$subjectName}\" has been deleted.",
            route('subjects.index')
        );
    }

    // ─── Question generation notifications ───

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
            'success',
            "{$count} {$type}(s) generated",
            "New {$type} questions are ready for practice.",
            $link
        );
    }

    public function notifyQuestionUpdated(int $userId, string $type): void
    {
        $this->create(
            $userId,
            'info',
            ucfirst($type) . ' updated',
            "Your {$type} question has been updated."
        );
    }

    public function notifyQuestionDeleted(int $userId, string $type): void
    {
        $this->create(
            $userId,
            'warning',
            ucfirst($type) . ' deleted',
            "A {$type} question has been deleted."
        );
    }

    // ─── Study plan notifications ───

    public function notifyStudyPlanGenerated(int $userId): void
    {
        $this->create(
            $userId,
            'success',
            'Study plan generated',
            'Your personalized study plan is ready.',
            route('study-plans.index')
        );
    }

    public function notifyStudyPlanDeleted(int $userId): void
    {
        $this->create(
            $userId,
            'warning',
            'Study plan deleted',
            'A study plan has been deleted.',
            route('study-plans.index')
        );
    }

    // ─── Quiz attempt notifications ───

    public function notifyQuizStarted(int $userId, string $title): void
    {
        $this->create(
            $userId,
            'info',
            'Quiz started',
            "You started \"{$title}\". Good luck!",
            route('quiz-attempts.index')
        );
    }

    public function notifyQuizSubmitted(int $userId, string $title, int $score): void
    {
        $this->create(
            $userId,
            'success',
            'Quiz completed',
            "You completed \"{$title}\" with score {$score}%.",
            route('quiz-attempts.index')
        );
    }

    public function notifyQuizDeleted(int $userId): void
    {
        $this->create(
            $userId,
            'warning',
            'Quiz attempt deleted',
            'A quiz attempt has been deleted.',
            route('quiz-attempts.index')
        );
    }

    // ─── Exam notifications ───

    public function notifyExamAdded(int $userId, string $examTitle): void
    {
        $this->create(
            $userId,
            'success',
            'Exam added',
            "Exam \"{$examTitle}\" has been added to your calendar.",
            route('exams.index')
        );
    }

    public function notifyExamUpdated(int $userId, string $examTitle): void
    {
        $this->create(
            $userId,
            'info',
            'Exam updated',
            "Exam \"{$examTitle}\" has been updated.",
            route('exams.index')
        );
    }

    public function notifyExamDeleted(int $userId, string $examTitle): void
    {
        $this->create(
            $userId,
            'warning',
            'Exam deleted',
            "Exam \"{$examTitle}\" has been removed.",
            route('exams.index')
        );
    }

    // ─── Time tracking notifications ───

    public function notifyTimerStarted(int $userId): void
    {
        $this->create(
            $userId,
            'info',
            'Timer started',
            'Your study timer has been started.',
            route('time-entries.index')
        );
    }

    public function notifyTimerStopped(int $userId, int $minutes): void
    {
        $this->create(
            $userId,
            'success',
            'Timer stopped',
            "You studied for {$minutes} minutes in this session.",
            route('time-entries.index')
        );
    }

    public function notifyTimeEntryDeleted(int $userId): void
    {
        $this->create(
            $userId,
            'warning',
            'Time entry deleted',
            'A time entry has been deleted.',
            route('time-entries.index')
        );
    }

    // ─── Pomodoro notifications ───

    public function notifyPomodoroCompleted(int $userId, int $minutes, int $xp = 10): void
    {
        $this->create(
            $userId,
            'success',
            'Pomodoro completed',
            "Great focus! You completed a {$minutes}-minute session. +{$xp} XP",
            route('pomodoro.index')
        );
    }

    // ─── Study group notifications ───

    public function notifyGroupCreated(int $userId, string $groupName): void
    {
        $this->create(
            $userId,
            'success',
            'Group created',
            "Study group \"{$groupName}\" has been created.",
            route('study-groups.index')
        );
    }

    public function notifyGroupJoined(int $userId, string $groupName): void
    {
        $this->create(
            $userId,
            'info',
            'Group joined',
            "You joined study group \"{$groupName}\".",
            route('study-groups.index')
        );
    }

    public function notifyGroupLeft(int $userId, string $groupName): void
    {
        $this->create(
            $userId,
            'warning',
            'Group left',
            "You left study group \"{$groupName}\".",
            route('study-groups.index')
        );
    }

    public function notifyGroupDeleted(int $userId, string $groupName): void
    {
        $this->create(
            $userId,
            'warning',
            'Group deleted',
            "Study group \"{$groupName}\" has been deleted.",
            route('study-groups.index')
        );
    }

    public function notifyGroupResourceShared(int $userId, string $groupName): void
    {
        $this->create(
            $userId,
            'success',
            'Resource shared',
            "You shared a question with group \"{$groupName}\".",
            route('study-groups.index')
        );
    }

    public function notifyGroupResourceUnshared(int $userId, string $groupName): void
    {
        $this->create(
            $userId,
            'info',
            'Resource removed',
            "A resource has been removed from group \"{$groupName}\".",
            route('study-groups.index')
        );
    }

    // ─── Peer review notifications ───

    public function notifyPeerReviewSubmitted(int $userId): void
    {
        $this->create(
            $userId,
            'success',
            'Review submitted',
            'Your peer review has been submitted. +5 XP',
            route('peer-reviews.index')
        );
    }

    public function notifyResourceSharedToMembers(int $userId, string $groupName, string $resourceTitle, string $link): void
    {
        $this->create(
            $userId,
            'success',
            'New resource shared',
            "A question \"{$resourceTitle}\" was shared in group \"{$groupName}\".",
            $link
        );
    }

    public function notifyGroupJoinedToCreator(int $creatorId, string $joinerName, string $groupName, int $groupId): void
    {
        $this->create(
            $creatorId,
            'info',
            'New member joined',
            "{$joinerName} joined your group \"{$groupName}\".",
            route('study-groups.show', $groupId)
        );
    }

    public function notifyPeerReviewReceived(int $questionOwnerId, string $reviewerName, string $questionTitle): void
    {
        $this->create(
            $questionOwnerId,
            'info',
            'Review received',
            "{$reviewerName} reviewed your question \"{$questionTitle}\".",
            route('peer-reviews.index')
        );
    }

    // ─── Bookmark notifications ───

    public function notifyBookmarkAdded(int $userId): void
    {
        $this->create(
            $userId,
            'success',
            'Bookmark added',
            'Item has been bookmarked for later review.',
            route('bookmarks.index')
        );
    }

    public function notifyBookmarkRemoved(int $userId): void
    {
        $this->create(
            $userId,
            'info',
            'Bookmark removed',
            'Bookmark has been removed.',
            route('bookmarks.index')
        );
    }

    // ─── Shared question visibility ───

    public function notifyVisibilityToggled(int $userId, bool $isPublic): void
    {
        $msg = $isPublic ? 'Your question is now public and visible to others.' : 'Your question is now private.';
        $this->create(
            $userId,
            'info',
            'Visibility updated',
            $msg,
            route('shared-questions.index')
        );
    }

    // ─── Export notifications ───

    public function notifyExportCompleted(int $userId, string $format): void
    {
        $this->create(
            $userId,
            'success',
            'Export completed',
            "Your questions have been exported as {$format}.",
            route('export.form')
        );
    }

    // ─── AI settings notifications ───

    public function notifyAiSettingsUpdated(int $userId): void
    {
        $this->create(
            $userId,
            'info',
            'AI settings updated',
            'Your AI provider settings have been updated.',
            route('ai.settings')
        );
    }

    // ─── User management (admin) ───

    public function notifyUserCreated(int $userId, string $userName): void
    {
        $this->create(
            $userId,
            'success',
            'User created',
            "User \"{$userName}\" has been created.",
            route('users.index')
        );
    }

    public function notifyUserUpdated(int $userId, string $userName): void
    {
        $this->create(
            $userId,
            'info',
            'User updated',
            "User \"{$userName}\" has been updated.",
            route('users.index')
        );
    }

    public function notifyUserDeleted(int $userId, string $userName): void
    {
        $this->create(
            $userId,
            'warning',
            'User deleted',
            "User \"{$userName}\" has been deleted.",
            route('users.index')
        );
    }

    // ─── Helper: Notification type icon map ───

    public static function getTypeMeta(?string $type = null): array
    {
        $map = [
            'success' => ['icon' => 'bi-check-circle-fill', 'color' => '#059669', 'bg' => '#ecfdf5'],
            'error'   => ['icon' => 'bi-exclamation-circle-fill', 'color' => '#dc2626', 'bg' => '#fef2f2'],
            'warning' => ['icon' => 'bi-exclamation-triangle-fill', 'color' => '#d97706', 'bg' => '#fffbeb'],
            'info'    => ['icon' => 'bi-info-circle-fill', 'color' => '#6366f1', 'bg' => '#eef2ff'],
        ];
        if ($type && isset($map[$type])) return $map[$type];
        return $map['info'];
    }

    public static function getTypeIcon(string $type): string
    {
        return self::getTypeMeta($type)['icon'];
    }

    public static function getTypeColor(string $type): string
    {
        return self::getTypeMeta($type)['color'];
    }

    public static function getTypeBg(string $type): string
    {
        return self::getTypeMeta($type)['bg'];
    }
}
