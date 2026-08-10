<?php

declare(strict_types=1);

namespace Deyvo\Core\Tests;

use Deyvo\Core\Http\Middleware\RequestIdMiddleware;
use Deyvo\Core\Support\Feature;
use Deyvo\Core\Support\Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

final class CoreTest extends TestCase
{
    public function test_health_endpoint_is_available(): void
    {
        self::assertContains(RequestIdMiddleware::class, $this->app['router']->getMiddlewareGroups()['web']);
        self::assertContains(
            RequestIdMiddleware::class,
            $this->app['router']->gatherRouteMiddleware(Route::getRoutes()->getByName('deyvo.health'))
        );
        self::assertFalse($this->app->shouldSkipMiddleware());

        $response = $this->app['router']->dispatch(Request::create('/_deyvo/health'));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('{"status":"ok"}', $response->getContent());
        self::assertTrue($response->headers->has('X-Request-ID'));
        self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    public function test_feature_flags_read_package_configuration(): void
    {
        config()->set('deyvo-core.features.dashboard', true);

        self::assertTrue(Feature::enabled('dashboard'));
        self::assertFalse(Feature::enabled('unknown'));
    }

    public function test_locale_and_timezone_configuration_are_applied_to_web_routes(): void
    {
        config()->set('deyvo-core.locale.default', 'nl');
        config()->set('deyvo-core.locale.supported', ['nl', 'en']);
        config()->set('deyvo-core.timezone.default', 'Europe/Amsterdam');

        Route::middleware('web')->get('/deyvo-core-locale', static fn () => response()->json([
            'locale' => app()->getLocale(),
            'timezone' => date_default_timezone_get(),
        ]));

        $response = $this->app['router']->dispatch(Request::create('/deyvo-core-locale'));

        self::assertSame('nl', $response->getData(true)['locale']);
        self::assertSame('Europe/Amsterdam', $response->getData(true)['timezone']);
    }

    public function test_flash_messages_are_stored_by_type(): void
    {
        Flash::success('Opgeslagen');
        Flash::success('Verzonden');

        self::assertSame(['Opgeslagen', 'Verzonden'], session('deyvo.flash.success'));
    }

    public function test_components_render(): void
    {
        $this->blade('<x-deyvo::button>Opslaan</x-deyvo::button>')
            ->assertSee('Opslaan')
            ->assertSee('bg-neutral-950');
    }

    public function test_package_test_view_renders(): void
    {
        $this->view('deyvo::test')
            ->assertSee('Deyvo Core werkt')
            ->assertSee('Gedeeld formulier')
            ->assertSee('Health endpoint');
    }

    public function test_web_middleware_preserves_application_headers(): void
    {
        Route::middleware('web')->get('/deyvo-core-header', static fn () => response('ok')->header('X-Frame-Options', 'DENY'));

        $response = $this->app['router']->dispatch(Request::create('/deyvo-core-header'));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('DENY', $response->headers->get('X-Frame-Options'));
    }
}
