<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\Cors::class);
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'login',
            'register',
            'logout',
            'chat/*',
            'documents/*',
            'subjects/*',
            'mcqs/*',
            'true-false/*',
            'short-answers/*',
            'fill-blanks/*',
            'matching/*',
            'flashcards/*',
            'quiz-attempts/*',
            'study-plans/*',
            'bookmarks/*',
            'exams/*',
            'time-entries/*',
            'pomodoro/*',
            'notifications/*',
            'study-groups/*',
            'shared-questions/*',
            'peer-reviews/*',
            'search',
            'export/*',
            'explain-answer',
            'dashboard',
            'profile',
            'password',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
