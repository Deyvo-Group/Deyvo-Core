<?php

declare(strict_types=1);

namespace Deyvo\Core\Providers;

use Deyvo\Core\Dashboard\DashboardManager;
use Deyvo\Core\Console\Commands\ImportLegacyCmsCommand;
use Deyvo\Core\Console\Commands\SeedCmsCommand;
use Deyvo\Core\Http\Middleware\LocaleMiddleware;
use Deyvo\Core\Http\Middleware\RequestIdMiddleware;
use Deyvo\Core\Http\Middleware\SecurityHeadersMiddleware;
use Deyvo\Core\Pages\PageManager;
use Deyvo\Core\Support\Actor;
use Deyvo\Core\Support\AuditLogger;
use Deyvo\Core\Support\Feature;
use Deyvo\Core\Support\Maintenance;
use Deyvo\Core\Support\HtmlSanitizer;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DashboardManager::class);
        $this->app->singleton(PageManager::class);
        $this->app->singleton(Actor::class);
        $this->app->singleton(AuditLogger::class);
        $this->app->singleton(HtmlSanitizer::class);

        $this->mergeRecursiveConfigFrom(__DIR__.'/../../config/core.php', 'deyvo-core');
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
        Blade::directive('deyvoEditable', static fn (string $expression): string => "<?php echo app(\\Deyvo\\Core\\Pages\\PageContent::class)->editable({$expression}); ?>");
        Blade::directive('deyvoEditor', static fn (): string => '<?php echo app(\\Deyvo\\Core\\Pages\\PageContent::class)->editor(); ?>');

        $this->app->booted(function () use ($router): void {
            $this->registerMiddleware($router);
        });

        if (config('deyvo-core.health.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../../routes/core.php');
        }

        if (config('deyvo-core.dashboard.enabled', false)) {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
            $this->loadRoutesFrom(__DIR__.'/../../routes/dashboard.php');

            if (config('deyvo-core.dashboard.pages.enabled', false)) {
                $this->loadRoutesFrom(__DIR__.'/../../routes/pages.php');
            }
        }

        $this->publishes([
            __DIR__.'/../../config/core.php' => config_path('deyvo-core.php'),
        ], 'deyvo-config');

        $this->publishes([
            __DIR__.'/../../resources/dashboard-schema.json' => resource_path('deyvo/dashboard.json'),
        ], 'deyvo-dashboard-schema');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportLegacyCmsCommand::class,
                SeedCmsCommand::class,
            ]);
        }
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

    private function mergeRecursiveConfigFrom(string $path, string $key): void
    {
        $defaults = require $path;
        $configured = $this->app['config']->get($key, []);

        $this->app['config']->set(
            $key,
            $this->mergeConfig($defaults, is_array($configured) ? $configured : []),
        );
    }

    private function mergeConfig(array $defaults, array $configured): array
    {
        foreach ($configured as $key => $value) {
            if (
                is_array($value)
                && isset($defaults[$key])
                && is_array($defaults[$key])
                && ! array_is_list($value)
                && ! array_is_list($defaults[$key])
            ) {
                $defaults[$key] = $this->mergeConfig($defaults[$key], $value);

                continue;
            }

            $defaults[$key] = $value;
        }

        return $defaults;
    }
}
