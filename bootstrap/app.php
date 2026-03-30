<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register middleware for API routes
        $middleware->api(append: [
            \App\Http\Middleware\BlockBrowserAccess::class,
        ]);

        // Register middleware alias
       $middleware->alias([
    'admin' => \App\Http\Middleware\AdminMiddleware::class,
    'tasker.auth' => \App\Http\Middleware\TaskerAuthMiddleware::class,
    ]);

    })

    ->withMiddleware(function (Middleware $middleware) {
    $middleware->statefulApi();

    // Configure API authentication to return JSON
    $middleware->redirectGuestsTo(function ($request) {
        if ($request->expectsJson() || $request->is('api/*')) {
            abort(response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please login first.'
            ], 401));
        }
        return route('login');
    });
})


    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
