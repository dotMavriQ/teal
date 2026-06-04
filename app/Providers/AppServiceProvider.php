<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
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
        $trustedProxies = config('app.trusted_proxies', '');
        Request::setTrustedProxies(
            explode(',', is_string($trustedProxies) ? $trustedProxies : ''),
            Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO |
            Request::HEADER_X_FORWARDED_PREFIX
        );

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            $appUrl = config('app.url');
            URL::forceRootUrl(is_string($appUrl) ? $appUrl : null);
        }
    }
}
