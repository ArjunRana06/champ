<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FillBlankController;
use App\Http\Controllers\FlashcardController;
use App\Http\Controllers\MatchingQuestionController;
use App\Http\Controllers\McqController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PeerReviewController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PomodoroController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizAttemptController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SharedQuestionBankController;
use App\Http\Controllers\ShortAnswerController;
use App\Http\Controllers\StudyGroupController;
use App\Http\Controllers\StudyPlanController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\TrueFalseController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Frontend routes (public pages)
Route::get('/', function () {
    return view('Frontend.Layout.welcome'); // home page
})->name('home');

// Dashboard & auth (protected)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // backend routes (admin only)
    Route::resource('users', UserController::class)->middleware('role:Admin');
    Route::resource('/roles', RoleController::class)->middleware('role:Admin');
    Route::resource('/permissions', PermissionController::class)->middleware('role:Admin');

    Route::resource('subjects', SubjectController::class);

    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents/upload', [DocumentController::class, 'upload'])->name('documents.upload');
    Route::get('/documents/{id}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::get('/documents/{id}/status', [DocumentController::class, 'status'])->name('documents.status');
    Route::post('/documents/{id}/retry', [DocumentController::class, 'retry'])->name('documents.retry');
    Route::delete('/documents/{id}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    Route::delete('/mcqs/delete-all', [McqController::class, 'destroyAll'])->name('mcqs.delete-all');
    Route::resource('mcqs', McqController::class)->except(['show', 'store']);
    Route::post('/mcqs/generate', [McqController::class, 'generate'])->name('mcqs.generate');

    Route::delete('/true-false/delete-all', [TrueFalseController::class, 'destroyAll'])->name('true-false.delete-all');
    Route::resource('true-false', TrueFalseController::class)->except(['show', 'store']);
    Route::post('/true-false/generate', [TrueFalseController::class, 'generate'])->name('true-false.generate');

    Route::delete('/short-answers/delete-all', [ShortAnswerController::class, 'destroyAll'])->name('short-answers.delete-all');
    Route::resource('short-answers', ShortAnswerController::class)->except(['show', 'store']);
    Route::post('/short-answers/generate', [ShortAnswerController::class, 'generate'])->name('short-answers.generate');

    Route::delete('/fill-blanks/delete-all', [FillBlankController::class, 'destroyAll'])->name('fill-blanks.delete-all');
    Route::resource('fill-blanks', FillBlankController::class)->except(['show', 'store']);
    Route::post('/fill-blanks/generate', [FillBlankController::class, 'generate'])->name('fill-blanks.generate');

    Route::delete('/matching/delete-all', [MatchingQuestionController::class, 'destroyAll'])->name('matching.delete-all');
    Route::resource('matching', MatchingQuestionController::class)->except(['show', 'store']);
    Route::post('/matching/generate', [MatchingQuestionController::class, 'generate'])->name('matching.generate');

    Route::delete('/flashcards/delete-all', [FlashcardController::class, 'destroyAll'])->name('flashcards.delete-all');
    Route::resource('flashcards', FlashcardController::class)->except(['show', 'store']);
    Route::post('/flashcards/generate', [FlashcardController::class, 'generate'])->name('flashcards.generate');
    Route::post('/flashcards/{flashcard}/review', [FlashcardController::class, 'review'])->name('flashcards.review');

    Route::get('/search', [SearchController::class, 'search'])->name('search');
    Route::get('/search/history', [SearchController::class, 'history'])->name('search.history');
    Route::delete('/search/history/{id}', [SearchController::class, 'destroy'])->name('search.history.destroy');
    Route::delete('/search/history', [SearchController::class, 'clear'])->name('search.history.clear');

    Route::post('/documents/{id}/summarize', [DocumentController::class, 'summarize'])->name('documents.summarize');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::delete('/notifications/{id}', [NotificationController::class, 'deleteNotification'])->name('notifications.delete');
    Route::delete('/notifications', [NotificationController::class, 'clearAll'])->name('notifications.clear-all');

    Route::get('/study-plans', [StudyPlanController::class, 'index'])->name('study-plans.index');
    Route::get('/study-plans/create', [StudyPlanController::class, 'create'])->name('study-plans.create');
    Route::post('/study-plans/generate', [StudyPlanController::class, 'generate'])->name('study-plans.generate');
    Route::get('/study-plans/{study_plan}', [StudyPlanController::class, 'show'])->name('study-plans.show');
    Route::delete('/study-plans/{study_plan}', [StudyPlanController::class, 'destroy'])->name('study-plans.destroy');

    Route::get('/quiz-attempts', [QuizAttemptController::class, 'index'])->name('quiz-attempts.index');
    Route::get('/quiz-attempts/create', [QuizAttemptController::class, 'create'])->name('quiz-attempts.create');
    Route::post('/quiz-attempts/start', [QuizAttemptController::class, 'start'])->name('quiz-attempts.start');
    Route::get('/quiz-attempts/{quiz_attempt}/take', [QuizAttemptController::class, 'take'])->name('quiz-attempts.take');
    Route::post('/quiz-attempts/{quiz_attempt}/submit', [QuizAttemptController::class, 'submit'])->name('quiz-attempts.submit');
    Route::get('/quiz-attempts/{quiz_attempt}/results', [QuizAttemptController::class, 'results'])->name('quiz-attempts.results');
    Route::delete('/quiz-attempts/{quiz_attempt}', [QuizAttemptController::class, 'destroy'])->name('quiz-attempts.destroy');

    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::post('/bookmarks/toggle', [BookmarkController::class, 'toggle'])->name('bookmarks.toggle');
    Route::delete('/bookmarks/{id}', [BookmarkController::class, 'destroy'])->name('bookmarks.destroy');

    Route::post('/chat', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/history', [ChatController::class, 'loadHistory'])->name('chat.history');
    Route::post('/chat/regenerate', [ChatController::class, 'regenerate'])->name('chat.regenerate');
    Route::post('/chat/clear', [ChatController::class, 'clearChat'])->name('chat.clear');
    Route::get('/ai-chat', [ChatController::class, 'showChatPage'])->name('ai.chat');
    Route::post('/chat/explain', [ChatController::class, 'explainAnswer'])->name('chat.explain');
    Route::get('/chat/conversations', [ChatController::class, 'conversations'])->name('chat.conversations');
    Route::get('/chat/conversations/{id}', [ChatController::class, 'showConversation'])->name('chat.conversations.show');
    Route::patch('/chat/conversations/{id}', [ChatController::class, 'renameConversation'])->name('chat.conversations.rename');
    Route::delete('/chat/conversations/{id}', [ChatController::class, 'destroyConversation'])->name('chat.conversations.destroy');

    Route::get('/ai-settings', [ChatController::class, 'showSettings'])->name('ai.settings')->middleware('role:Admin');
    Route::post('/ai-settings', [ChatController::class, 'updateSettings'])->name('ai.settings.update')->middleware('role:Admin');

    // Exam Calendar
    Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/create', [ExamController::class, 'create'])->name('exams.create');
    Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');
    Route::put('/exams/{exam}', [ExamController::class, 'update'])->name('exams.update');
    Route::delete('/exams/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy');

    // Time Tracking
    Route::get('/time-entries', [TimeEntryController::class, 'index'])->name('time-entries.index');
    Route::post('/time-entries/start', [TimeEntryController::class, 'start'])->name('time-entries.start');
    Route::post('/time-entries/stop', [TimeEntryController::class, 'stop'])->name('time-entries.stop');
    Route::post('/time-entries/discard', [TimeEntryController::class, 'discard'])->name('time-entries.discard');
    Route::delete('/time-entries/{time_entry}', [TimeEntryController::class, 'destroy'])->name('time-entries.destroy');

    // Pomodoro Timer
    Route::get('/pomodoro', [PomodoroController::class, 'index'])->name('pomodoro.index');
    Route::post('/pomodoro/complete', [PomodoroController::class, 'complete'])->name('pomodoro.complete');

    // Export
    Route::get('/export', [ExportController::class, 'form'])->name('export.form');
    Route::post('/export/pdf', [ExportController::class, 'exportPdf'])->name('export.pdf');
    Route::post('/export/csv', [ExportController::class, 'exportCsv'])->name('export.csv');
    Route::post('/export/json', [ExportController::class, 'exportJson'])->name('export.json');
    Route::post('/export/anki', [ExportController::class, 'exportAnki'])->name('export.anki');

    // Study Groups
    Route::get('/study-groups', [StudyGroupController::class, 'index'])->name('study-groups.index');
    Route::post('/study-groups', [StudyGroupController::class, 'store'])->name('study-groups.store');
    Route::get('/study-groups/{studyGroup}', [StudyGroupController::class, 'show'])->name('study-groups.show');
    Route::post('/study-groups/{studyGroup}/join', [StudyGroupController::class, 'join'])->name('study-groups.join');
    Route::post('/study-groups/{studyGroup}/leave', [StudyGroupController::class, 'leave'])->name('study-groups.leave');
    Route::post('/study-groups/{studyGroup}/share', [StudyGroupController::class, 'share'])->name('study-groups.share');
    Route::delete('/study-groups/{studyGroup}/share/{resource}', [StudyGroupController::class, 'unshare'])->name('study-groups.unshare');
    Route::put('/study-groups/{studyGroup}', [StudyGroupController::class, 'update'])->name('study-groups.update');
    Route::delete('/study-groups/{studyGroup}', [StudyGroupController::class, 'destroy'])->name('study-groups.destroy');
    Route::post('/study-groups/{studyGroup}/members/{member}/remove', [StudyGroupController::class, 'removeMember'])->name('study-groups.remove-member');
    Route::post('/study-groups/{studyGroup}/members/{member}/role', [StudyGroupController::class, 'updateMemberRole'])->name('study-groups.member-role');

    // Shared Question Banks
    Route::get('/shared-questions', [SharedQuestionBankController::class, 'index'])->name('shared-questions.index');
    Route::get('/shared-questions/fetch', [SharedQuestionBankController::class, 'fetchMore'])->name('shared-questions.fetch');

    // Peer Reviews
    Route::get('/peer-reviews', [PeerReviewController::class, 'index'])->name('peer-reviews.index');
    Route::post('/peer-reviews', [PeerReviewController::class, 'store'])->name('peer-reviews.store');
    Route::delete('/peer-reviews/{peerReview}', [PeerReviewController::class, 'destroy'])->name('peer-reviews.destroy');

    // Offline page
    Route::get('/offline', function () {
        return view('Backend.offline');
    })->name('offline');

});

require __DIR__.'/auth.php';
