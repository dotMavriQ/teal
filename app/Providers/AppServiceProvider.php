<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
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
        // N+1 guardrail: outside production, lazy-loading a relation that wasn't
        // eager-loaded throws instead of silently firing a query per row. Catches
        // performance regressions in dev/CI before they ship.
        Model::preventLazyLoading(! $this->app->isProduction());

        // Trust proxies from container/private networks only (Traefik/subpath)
        $trustedProxies = env('TRUSTED_PROXIES', '10.0.0.0/8,172.16.0.0/12,192.168.0.0/16,fd00::/8');
        \Illuminate\Http\Request::setTrustedProxies(
            explode(',', is_string($trustedProxies) ? $trustedProxies : ''),
            \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PREFIX
        );

        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
            $appUrl = config('app.url');
            \Illuminate\Support\Facades\URL::forceRootUrl(is_string($appUrl) ? $appUrl : null);
        }
    }
}
