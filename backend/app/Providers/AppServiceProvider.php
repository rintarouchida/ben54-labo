<?php

declare(strict_types=1);

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
        // throttle:api のリミッター(要件定義書 7.4節: 1IPあたり毎分60回)
        RateLimiter::for('api', fn (Request $request): Limit => Limit::perMinute(60)->by((string) $request->ip()));
    }
}
