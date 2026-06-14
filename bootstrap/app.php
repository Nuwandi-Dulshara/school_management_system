<?php

use App\Http\Middleware\EnsureUserIsSuperAdmin;
use App\Http\Middleware\EnsureUserCanManageAttendance;
use App\Http\Middleware\EnsureUserCanManageExaminations;
use App\Http\Middleware\EnsureUserCanManageMarks;
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
        $middleware->alias([
            'super_admin' => EnsureUserIsSuperAdmin::class,
            'attendance_manager' => EnsureUserCanManageAttendance::class,
            'examination_manager' => EnsureUserCanManageExaminations::class,
            'marks_manager' => EnsureUserCanManageMarks::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
