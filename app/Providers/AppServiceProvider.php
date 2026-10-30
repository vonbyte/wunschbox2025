<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // No script may flood the box with uploads. Counted per IP, so guests on the same network
        // (a family Wi-Fi at the party) share the limit, hence the room for several of them
        RateLimiter::for('wishbox', fn (Request $request) => [
            Limit::perMinute(10)->by('minute:'.$request->ip()),
            Limit::perHour(60)->by('hour:'.$request->ip()),
        ]);
    }
}
