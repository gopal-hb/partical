<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function ($exceptions) {

    $exceptions->render(function (
        \Illuminate\Auth\AuthenticationException $e,
        $request
    ) {

        if ($request->is('api/*')) {

            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);

        }

    });

})->booted(function () {

        RateLimiter::for('api', function (Request $request) {

            return Limit::perMinute(60)->by(
                $request->user()?->id ?: $request->ip()
            );

        });

    })
    ->create();
