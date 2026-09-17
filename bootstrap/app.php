<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\RobotsHeaders;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__.'/../routes/admin.php',
            __DIR__.'/../routes/web.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [SecurityHeaders::class]);
        $middleware->append(RobotsHeaders::class);
        $middleware->alias(['admin' => EnsureUserIsAdmin::class]);
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*', 'process/*') || $request->expectsJson(),
        );

        $exceptions->render(fn (PostTooLargeException $e, Request $request) => $request->is('process/*')
            ? response()->json(['message' => 'Your image exceeds the maximum allowed file size.'], 413)
            : null);
    })->create();
