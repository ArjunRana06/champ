<?php

use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\DashboardController;

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

    // backend routes (admin panel)
    Route::resource('users', UserController::class);
    Route::resource('/roles', RoleController::class);
    Route::resource('/permissions', PermissionController::class);

    Route::resource('/events', EventController::class);
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::get('/events/filter/{type}', [EventController::class, 'filterByType'])->name('events.filter');


    Route::post('/chat', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/ai-chat', [ChatController::class, 'showChatPage'])->name('ai.chat');

});



require __DIR__ . '/auth.php';
