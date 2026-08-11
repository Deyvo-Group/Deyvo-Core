<?php

declare(strict_types=1);

namespace Deyvo\Core\Tests;

use Deyvo\Core\Providers\CoreServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:MTIzNDU2Nzg5MDEyMzQ1Njc4OTAxMjM0NTY3ODkwMTI=');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('deyvo-core.dashboard', [
            'enabled' => true,
            'path' => 'deyvo',
            'middleware' => ['web'],
            'pages' => [
                'enabled' => true,
            ],
            'navigation' => [
                [
                    'label' => 'Overzicht',
                    'route' => 'deyvo.dashboard.index',
                    'active' => 'deyvo.dashboard.index',
                    'sort' => 10,
                ],
                [
                    'label' => 'Content',
                    'route' => 'deyvo.dashboard.contents.index',
                    'active' => 'deyvo.dashboard.contents.*',
                    'sort' => 20,
                ],
                [
                    'label' => 'Instellingen',
                    'route' => 'deyvo.dashboard.settings.index',
                    'active' => 'deyvo.dashboard.settings.*',
                    'sort' => 30,
                ],
            ],
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            CoreServiceProvider::class,
        ];
    }
}
