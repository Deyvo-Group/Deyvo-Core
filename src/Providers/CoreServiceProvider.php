<?php

declare(strict_types=1);

namespace Deyvo\Core\Providers;

use Deyvo\Core\Dashboard\DashboardManager;
use Deyvo\Core\Http\Middleware\LocaleMiddleware;
use Deyvo\Core\Http\Middleware\RequestIdMiddleware;
use Deyvo\Core\Http\Middleware\SecurityHeadersMiddleware;
use Deyvo\Core\Support\Feature;
use Deyvo\Core\Support\Maintenance;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DashboardManager::class);

        $this->mergeConfigFrom(
            __DIR__.'/../../config/core.php',
            'deyvo-core'
        );
    }

    public function boot(Router $router): void
    {
        $this->loadViewsFrom(
            __DIR__.'/../../resources/views',
            'deyvo'
        );

        Blade::anonymousComponentPath(
            __DIR__.'/../../resources/views/components',
            'deyvo'
        );

        Blade::if('deyvoFeature', static fn (string $name): bool => Feature::enabled($name));
        Blade::if('deyvoMaintenance', static fn (): bool => Maintenance::active());

        $this->app->booted(function () use ($router): void {
            $this->registerMiddleware($router);
        });

        if (config('deyvo-core.health.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../../routes/core.php');
        }

        if (config('deyvo-core.dashboard.enabled', false)) {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
            $this->loadRoutesFrom(__DIR__.'/../../routes/dashboard.php');
        }

        $this->publishes([
            __DIR__.'/../../config/core.php' => config_path('deyvo-core.php'),
        ], 'deyvo-config');
    }

    private function registerMiddleware(Router $router): void
    {
        $group = config('deyvo-core.middleware_group', 'web');

        $localeEnabled = config('deyvo-core.locale.enabled', true);
        $timezoneEnabled = config('deyvo-core.timezone.enabled', true);

        if ($localeEnabled || $timezoneEnabled) {
            $router->pushMiddlewareToGroup($group, LocaleMiddleware::class);
        }

        if (config('deyvo-core.request_id.enabled', true)) {
            $router->pushMiddlewareToGroup($group, RequestIdMiddleware::class);
        }

        if (config('deyvo-core.security_headers.enabled', true)) {
            $router->pushMiddlewareToGroup($group, SecurityHeadersMiddleware::class);
        }
    }
}
