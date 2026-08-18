<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Guests submitting task requests are throttled by IP to deter spam.
        // Authenticated callers bypass — they're identified by their JWT.
        RateLimiter::for('guest-task-request', function (Request $request) {
            return $request->user()
                ? Limit::none()
                : Limit::perMinute(5)->by($request->ip());
        });
    }
}
