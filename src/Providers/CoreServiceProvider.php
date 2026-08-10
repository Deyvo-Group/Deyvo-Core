<?php

declare(strict_types=1);

namespace Deyvo\Core\Providers;

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

    public function boot(): void
    {
        $this->loadViewsFrom(
            __DIR__.'/../../resources/views',
            'deyvo'
        );

        Blade::anonymousComponentPath(
            __DIR__.'/../../resources/views/components',
            'deyvo'
        );

        $this->publishes([
            __DIR__.'/../../config/core.php' => config_path('deyvo-core.php'),
        ], 'deyvo-config');
    }
}
