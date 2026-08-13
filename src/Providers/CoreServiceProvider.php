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
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        $this->registerNotFoundRenderer();

        $this->app->booted(function () use ($router): void {
            $this->registerMiddleware($router);
        });

        if (config('deyvo-core.health.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../../routes/core.php');
        }

        if (config('deyvo-core.dashboard.enabled', false)) {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
            $this->loadRoutesFrom(__DIR__.'/../../routes/dashboard.php');
            $this->loadRoutesFrom(__DIR__.'/../../routes/pages.php');
            $this->loadRoutesFrom(__DIR__.'/../../routes/dashboard-fallback.php');
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

    private function registerNotFoundRenderer(): void
    {
        $register = function (ExceptionHandler $handler): void {
            if (! method_exists($handler, 'renderable')) {
                return;
            }

            $handler->renderable(function (NotFoundHttpException $exception, Request $request) {
                $view = $this->notFoundViewFor($request);

                if ($view === '' || ! view()->exists($view)) {
                    return null;
                }

                return response()->view($view, [
                    'dashboardPath' => trim((string) config('deyvo-core.dashboard.path', 'deyvo'), '/'),
                    'exception' => $exception,
                    'request' => $request,
                ], 404);
            });
        };

        if ($this->app->resolved(ExceptionHandler::class)) {
            $register($this->app->make(ExceptionHandler::class));

            return;
        }

        $this->app->afterResolving(ExceptionHandler::class, $register);
    }

    private function notFoundViewFor(Request $request): string
    {
        if ($this->isDashboardRequest($request)) {
            return (string) config('deyvo-core.errors.dashboard_404_view', 'deyvo::dashboard.errors.404');
        }

        $view = (string) config('deyvo-core.errors.public_404_view', 'deyvo::errors.404');

        if ($view === 'deyvo::errors.404' && $this->public404LayoutExists()) {
            return 'deyvo::errors.404-with-layout';
        }

        return $view;
    }

    private function public404LayoutExists(): bool
    {
        $layout = (string) config('deyvo-core.errors.public_404_layout_view', 'layout.app');
        $section = (string) config('deyvo-core.errors.public_404_layout_section', 'content');

        if ($layout === '' || $section === '') {
            return false;
        }

        return view()->exists($layout);
    }

    private function isDashboardRequest(Request $request): bool
    {
        if (! config('deyvo-core.dashboard.enabled', false)) {
            return false;
        }

        $path = trim((string) config('deyvo-core.dashboard.path', 'deyvo'), '/');

        return $path !== '' && ($request->is($path) || $request->is($path.'/*'));
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
