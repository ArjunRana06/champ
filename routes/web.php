<?php

use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\McqController;

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
    Route::get('/documents/{id}/preview', [DocumentController::class, 'preview'])->name('documents.
    preview');

    Route::resource('mcqs', McqController::class)->except(['show', 'edit', 'update']);
Route::post('/mcqs/generate', [McqController::class, 'generate'])->name('mcqs.generate');


    Route::post('/chat', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/ai-chat', [ChatController::class, 'showChatPage'])->name('ai.chat');

});

require __DIR__ . '/auth.php';
