<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\RedirectResponse;
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
            Limit::perMinute(10)->by('minute:'.$request->ip())->response($this->backToWishbox(...)),
            Limit::perHour(60)->by('hour:'.$request->ip())->response($this->backToWishbox(...)),
        ]);
    }

    /**
     * Over the limit, guests get the form back with their wish as typed and how long to wait,
     * instead of an error page that throws the wish away
     */
    private function backToWishbox(Request $request, array $headers): RedirectResponse
    {
        $minutes = (int) ceil($headers['Retry-After'] / 60);

        $message = trans_choice('A lot of wishes arrived at once. Please wait one minute and send your wish again.|A lot of wishes arrived at once. Please wait :count minutes and send your wish again.', $minutes);

        // Browsers can not refill a file input, so the image has to be picked again
        if ($request->hasFile('image')) {
            $message .= ' '.__('Your text is still here, only the picture has to be attached again.');
        }

        return redirect()->route('wishbox')->withInput()->withErrors(['throttle' => $message]);
    }
}
