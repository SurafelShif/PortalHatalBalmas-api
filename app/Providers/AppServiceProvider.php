<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Symfony\Component\HttpFoundation\Response;

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
        Passport::enablePasswordGrant();
        Passport::tokensExpireIn(now()->addMinutes(config('auth.passport_tokens.access_expires_in')));
        Passport::refreshTokensExpireIn(now()->addDays(config('auth.passport_tokens.refresh_expires_in')));

        $this->configureRateLimiting();
    }

    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function ($request) {
            return Limit::perMinute(config('app.rate_limitation'))->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response('בקשות מרובות לשרת. נא לנסות שוב מאוחר יותר', Response::HTTP_TOO_MANY_REQUESTS, $headers);
                });
        });
    }
}
