<?php

declare(strict_types=1);

namespace Deyvo\Core\Providers;

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

        $this->publishes([
            __DIR__.'/../../config/core.php' => config_path('deyvo-core.php'),
        ], 'deyvo-config');
    }

    private function registerMiddleware(Router $router): void
    {
        $group = config('deyvo-core.middleware_group', 'web');

        foreach ([
            'deyvo-core.locale.enabled' => LocaleMiddleware::class,
            'deyvo-core.request_id.enabled' => RequestIdMiddleware::class,
            'deyvo-core.security_headers.enabled' => SecurityHeadersMiddleware::class,
        ] as $configuration => $middleware) {
            if (config($configuration, true)) {
                $router->pushMiddlewareToGroup($group, $middleware);
            }
        }
    }
}
