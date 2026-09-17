<?php

namespace App\Providers;

use App\Services\ImageCompression\GdImageCompressor;
use App\Tools\ToolRegistry;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ToolRegistry::class, fn () => new ToolRegistry(
            config('tools.pages'),
            config('tools.content_path'),
            config('tools.content_defaults_path'),
        ));

        $this->app->bind(GdImageCompressor::class, fn () => new GdImageCompressor(
            (int) config('compressor.server_max_pixels'),
        ));
    }

    public function boot(): void
    {
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        RateLimiter::for('compress', fn (Request $request) => Limit::perMinute(config('compressor.rate_limit_per_minute'))
            ->by($request->ip())
            ->response(fn () => response()->json([
                'message' => 'You have made too many compression requests. Please wait a minute and try again.',
            ], 429)));

        RateLimiter::for('contact', fn (Request $request) => Limit::perMinutes(10, 5)
            ->by($request->ip())
            ->response(fn () => back()->withInput()->withErrors([
                'form' => 'You have sent several messages recently. Please wait a few minutes before trying again.',
            ])));
    }
}
