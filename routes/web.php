<?php

use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\McqController;
use App\Http\Controllers\TrueFalseController;
use App\Http\Controllers\ShortAnswerController;
use App\Http\Controllers\FillBlankController;
use App\Http\Controllers\MatchingQuestionController;
use App\Http\Controllers\FlashcardController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StudyPlanController;
use App\Http\Controllers\QuizAttemptController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\PomodoroController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ExplainAnswerController;
use App\Http\Controllers\StudyGroupController;
use App\Http\Controllers\SharedQuestionBankController;
use App\Http\Controllers\PeerReviewController;

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
    Route::delete('/documents/{id}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    Route::resource('mcqs', McqController::class)->except(['show']);
Route::post('/mcqs/generate', [McqController::class, 'generate'])->name('mcqs.generate');

    Route::resource('true-false', TrueFalseController::class)->except(['show', 'store']);
Route::post('/true-false/generate', [TrueFalseController::class, 'generate'])->name('true-false.generate');

    Route::resource('short-answers', ShortAnswerController::class)->except(['show', 'store']);
Route::post('/short-answers/generate', [ShortAnswerController::class, 'generate'])->name('short-answers.generate');

    Route::resource('fill-blanks', FillBlankController::class)->except(['show', 'store']);
Route::post('/fill-blanks/generate', [FillBlankController::class, 'generate'])->name('fill-blanks.generate');

    Route::resource('matching', MatchingQuestionController::class)->except(['show', 'store']);
Route::post('/matching/generate', [MatchingQuestionController::class, 'generate'])->name('matching.generate');

    Route::resource('flashcards', FlashcardController::class)->except(['show', 'store']);
Route::post('/flashcards/generate', [FlashcardController::class, 'generate'])->name('flashcards.generate');


    Route::get('/search', [SearchController::class, 'search'])->name('search');

    Route::post('/documents/{id}/summarize', [DocumentController::class, 'summarize'])->name('documents.summarize');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');

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
    Route::get('/ai-chat', [ChatController::class, 'showChatPage'])->name('ai.chat');
    Route::post('/chat/explain', [ChatController::class, 'explainAnswer'])->name('chat.explain');

    // Exam Calendar
    Route::get('/exams', [App\Http\Controllers\ExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/create', [App\Http\Controllers\ExamController::class, 'create'])->name('exams.create');
    Route::post('/exams', [App\Http\Controllers\ExamController::class, 'store'])->name('exams.store');
    Route::put('/exams/{exam}', [App\Http\Controllers\ExamController::class, 'update'])->name('exams.update');
    Route::delete('/exams/{exam}', [App\Http\Controllers\ExamController::class, 'destroy'])->name('exams.destroy');

    // Time Tracking
    Route::get('/time-entries', [App\Http\Controllers\TimeEntryController::class, 'index'])->name('time-entries.index');
    Route::post('/time-entries/start', [App\Http\Controllers\TimeEntryController::class, 'start'])->name('time-entries.start');
    Route::post('/time-entries/stop', [App\Http\Controllers\TimeEntryController::class, 'stop'])->name('time-entries.stop');
    Route::delete('/time-entries/{time_entry}', [App\Http\Controllers\TimeEntryController::class, 'destroy'])->name('time-entries.destroy');

    // Pomodoro Timer
    Route::get('/pomodoro', [App\Http\Controllers\PomodoroController::class, 'index'])->name('pomodoro.index');
    Route::post('/pomodoro/complete', [App\Http\Controllers\PomodoroController::class, 'complete'])->name('pomodoro.complete');

    // Export
    Route::get('/export', [App\Http\Controllers\ExportController::class, 'form'])->name('export.form');
    Route::post('/export/pdf', [App\Http\Controllers\ExportController::class, 'exportPdf'])->name('export.pdf');
    Route::post('/export/csv', [App\Http\Controllers\ExportController::class, 'exportCsv'])->name('export.csv');
    Route::post('/export/json', [App\Http\Controllers\ExportController::class, 'exportJson'])->name('export.json');
    Route::post('/export/anki', [App\Http\Controllers\ExportController::class, 'exportAnki'])->name('export.anki');

    // Explain Answer
    Route::post('/explain-answer', [App\Http\Controllers\ExplainAnswerController::class, 'explain'])->name('explain-answer');

    // Study Groups
    Route::get('/study-groups', [App\Http\Controllers\StudyGroupController::class, 'index'])->name('study-groups.index');
    Route::post('/study-groups', [App\Http\Controllers\StudyGroupController::class, 'store'])->name('study-groups.store');
    Route::get('/study-groups/{studyGroup}', [App\Http\Controllers\StudyGroupController::class, 'show'])->name('study-groups.show');
    Route::post('/study-groups/{studyGroup}/join', [App\Http\Controllers\StudyGroupController::class, 'join'])->name('study-groups.join');
    Route::post('/study-groups/{studyGroup}/leave', [App\Http\Controllers\StudyGroupController::class, 'leave'])->name('study-groups.leave');
    Route::post('/study-groups/{studyGroup}/share', [App\Http\Controllers\StudyGroupController::class, 'share'])->name('study-groups.share');
    Route::delete('/study-groups/{studyGroup}/share/{resource}', [App\Http\Controllers\StudyGroupController::class, 'unshare'])->name('study-groups.unshare');
    Route::delete('/study-groups/{studyGroup}', [App\Http\Controllers\StudyGroupController::class, 'destroy'])->name('study-groups.destroy');

    // Shared Question Banks
    Route::get('/shared-questions', [App\Http\Controllers\SharedQuestionBankController::class, 'index'])->name('shared-questions.index');
    Route::post('/shared-questions/toggle-visibility', [App\Http\Controllers\SharedQuestionBankController::class, 'toggleVisibility'])->name('shared-questions.toggle');

    // Peer Reviews
    Route::get('/peer-reviews', [App\Http\Controllers\PeerReviewController::class, 'index'])->name('peer-reviews.index');
    Route::post('/peer-reviews', [App\Http\Controllers\PeerReviewController::class, 'store'])->name('peer-reviews.store');

    // Offline page
    Route::get('/offline', function () { return view('Backend.offline'); })->name('offline');

});

require __DIR__ . '/auth.php';
