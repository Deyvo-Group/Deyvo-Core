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
    }

    protected function getPackageProviders($app): array
    {
        return [
            CoreServiceProvider::class,
        ];
    }
}
