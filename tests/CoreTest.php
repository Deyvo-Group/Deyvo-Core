<?php

declare(strict_types=1);

namespace Deyvo\Core\Tests;

use Deyvo\Core\Dashboard\DashboardManager;
use Deyvo\Core\Http\Middleware\RequestIdMiddleware;
use Deyvo\Core\Models\Content;
use Deyvo\Core\Models\AuditLog;
use Deyvo\Core\Models\Page;
use Deyvo\Core\Models\PageRevision;
use Deyvo\Core\Models\Setting;
use Deyvo\Core\Pages\PageManager;
use Deyvo\Core\Support\SiteContent;
use Deyvo\Core\Support\SiteSettings;
use Deyvo\Core\Support\Feature;
use Deyvo\Core\Support\Flash;
use Illuminate\Http\Request;
use Illuminate\Auth\GenericUser;
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
            ->assertSee('bg-gradient-to-br');
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

    public function test_dashboard_renders_its_overview(): void
    {
        $response = $this->app['router']->dispatch(Request::create('/deyvo'));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('Overzicht', $response->getContent());
        self::assertStringContainsString('Content toevoegen', $response->getContent());
    }

    public function test_dashboard_stores_content_and_settings(): void
    {
        $this->post('/deyvo/content', [
            'key' => 'homepage.intro',
            'title' => 'Welkom',
            'body' => 'Welkom bij Deyvo.',
            'is_published' => '1',
        ])->assertRedirect(route('deyvo.dashboard.contents.index'));
        $this->post('/deyvo/settings', [
            'key' => 'contact.email',
            'value' => 'hello@deyvo.test',
        ])->assertRedirect(route('deyvo.dashboard.settings.index'));

        self::assertSame(1, Content::query()->count());
        self::assertSame('Welkom bij Deyvo.', SiteContent::body('homepage.intro'));
        self::assertSame('hello@deyvo.test', SiteSettings::get('contact.email'));
        self::assertTrue(Content::query()->where('key', 'homepage.intro')->value('is_published'));
        self::assertSame(1, Setting::query()->count());

        $this->get('/deyvo/content')
            ->assertOk()
            ->assertSee('homepage.intro')
            ->assertSee('Welkom');
        $this->get('/deyvo/settings')
            ->assertOk()
            ->assertSee('contact.email')
            ->assertSee('hello@deyvo.test');
    }

    public function test_dashboard_records_attributed_activity_and_renders_it(): void
    {
        Route::post('/logout', static fn () => response('Uitgelogd'))->name('logout');
        Route::getRoutes()->refreshNameLookups();
        self::assertTrue(Route::has('logout'));

        $this->actingAs(new GenericUser([
            'id' => 7,
            'name' => 'Dirk Deyvo',
            'email' => 'dirk@deyvo.test',
        ]));

        $this->post('/deyvo/content', [
            'key' => 'homepage.activity',
            'title' => 'Activiteit',
            'body' => 'Wordt bijgehouden.',
            'is_published' => '1',
        ])->assertRedirect();

        $activity = AuditLog::query()->firstOrFail();

        self::assertSame('content.created', $activity->event);
        self::assertSame('Dirk Deyvo', $activity->actor_name);
        self::assertSame('dirk@deyvo.test', $activity->actor_email);
        self::assertSame('homepage.activity', $activity->subject_label);

        $this->get('/deyvo')
            ->assertOk()
            ->assertSee('Dirk Deyvo')
            ->assertSee('Uitloggen')
            ->assertSee('action="http://localhost/logout"', false)
            ->assertSee('Recente activiteit');
        $this->get('/deyvo/activity')
            ->assertOk()
            ->assertSee('Content aangemaakt')
            ->assertSee('Dirk Deyvo');
        $this->get("/deyvo/activity/{$activity->id}")
            ->assertOk()
            ->assertSee('Request-id')
            ->assertSee('homepage.activity');
    }

    public function test_dashboard_gradient_can_be_configured(): void
    {
        config()->set('deyvo-core.ui.dashboard.gradient', 'linear-gradient(135deg, #ffffff 0%, #dbeafe 100%)');

        $this->get('/deyvo')
            ->assertOk()
            ->assertSee('--deyvo-dashboard-gradient: linear-gradient(135deg, #ffffff 0%, #dbeafe 100%);', false);
    }

    public function test_dashboard_renders_and_saves_a_custom_json_schema(): void
    {
        app(DashboardManager::class)->registerSchema(json_encode([
            'pages' => [
                [
                    'key' => 'website',
                    'label' => 'Website',
                    'description' => 'Beheer de websitegegevens.',
                    'sort' => 40,
                    'fields' => [
                        [
                            'key' => 'contact.email',
                            'label' => 'E-mailadres',
                            'type' => 'email',
                            'required' => true,
                        ],
                        [
                            'key' => 'homepage.intro',
                            'label' => 'Introductie',
                            'type' => 'textarea',
                            'storage' => 'content',
                            'content_title' => 'Homepage introductie',
                        ],
                        [
                            'key' => 'website.status',
                            'label' => 'Status',
                            'type' => 'select',
                            'options' => [
                                [
                                    'value' => 'concept',
                                    'label' => 'Concept',
                                ],
                                [
                                    'value' => 'live',
                                    'label' => 'Live',
                                ],
                            ],
                        ],
                        [
                            'key' => 'contact.visible',
                            'label' => 'Toon contactgegevens',
                            'type' => 'boolean',
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        $this->get('/deyvo')
            ->assertOk()
            ->assertSee('Website');
        $this->get('/deyvo/custom/website')
            ->assertOk()
            ->assertSee('Beheer de websitegegevens.')
            ->assertSee('Toon contactgegevens');
        $this->put('/deyvo/custom/website', [
            'values' => [
                'hello@deyvo.test',
                'Welkom bij Deyvo.',
                'live',
                '1',
            ],
        ])->assertRedirect(route('deyvo.dashboard.custom.show', ['page' => 'website']));

        self::assertSame('hello@deyvo.test', SiteSettings::get('contact.email'));
        self::assertSame('Welkom bij Deyvo.', SiteContent::body('homepage.intro'));
        self::assertSame('live', SiteSettings::get('website.status'));
        self::assertSame('1', SiteSettings::get('contact.visible'));
        self::assertSame('Homepage introductie', Content::query()->where('key', 'homepage.intro')->value('title'));
        $this->get('/deyvo/custom/unknown')->assertNotFound();
    }

    public function test_dashboard_reads_a_schema_from_the_configured_json_path(): void
    {
        config()->set('deyvo-core.dashboard.schema.path', __DIR__.'/Fixtures/dashboard.json');

        $this->get('/deyvo')
            ->assertOk()
            ->assertSee('Zoekmachine');
        $this->get('/deyvo/custom/zoekmachine')
            ->assertOk()
            ->assertSee('Beheer de zichtbaarheid van de website.');
    }

    public function test_pages_support_drafts_publications_and_revision_restore(): void
    {
        $this->actingAs(new GenericUser([
            'id' => 8,
            'name' => 'Pagina Beheerder',
            'email' => 'pagina@deyvo.test',
        ]));

        app(DashboardManager::class)->registerSchema(json_encode([
            'pages' => [],
            'templates' => [
                [
                    'key' => 'landing',
                    'label' => 'Landingspagina',
                    'sort' => 10,
                    'sections' => [
                        [
                            'key' => 'hero',
                            'label' => 'Hero',
                            'fields' => [
                                [
                                    'key' => 'title',
                                    'label' => 'Titel',
                                    'type' => 'text',
                                    'required' => true,
                                ],
                                [
                                    'key' => 'intro',
                                    'label' => 'Introductie',
                                    'type' => 'textarea',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        $this->post('/deyvo/pages', [
            'title' => 'Homepage',
            'slug' => 'home',
            'template' => 'landing',
            'sections' => [
                'hero' => [
                    'title' => 'Welkom',
                    'intro' => 'Welkom bij Deyvo.',
                ],
            ],
            'seo' => [
                'title' => 'Deyvo',
                'description' => 'Digitale ervaringen.',
                'indexable' => '1',
            ],
        ])->assertRedirect();

        $page = Page::query()->firstOrFail();
        self::assertSame('home', $page->key);
        self::assertNotNull($page->draft_revision_id);
        self::assertNull($page->published_revision_id);
        self::assertSame('Welkom', PageRevision::query()->firstOrFail()->sections['hero']['title']);
        self::assertSame('Pagina Beheerder', PageRevision::query()->firstOrFail()->created_by_name);
        $this->get('/deyvo/pages')->assertOk()->assertSee('Concept')->assertSee('/home');

        $this->post("/deyvo/pages/{$page->id}/publish")->assertRedirect();
        $page->refresh();
        self::assertSame('home', $page->published_slug);
        self::assertNotNull($page->published_revision_id);
        self::assertNull($page->draft_revision_id);

        $this->put("/deyvo/pages/{$page->id}", [
            'title' => 'Nieuwe homepage',
            'slug' => 'home',
            'template' => 'landing',
            'sections' => [
                'hero' => [
                    'title' => 'Nieuwe titel',
                    'intro' => 'Nieuwe introductie.',
                ],
            ],
            'seo' => [
                'title' => 'Nieuwe Deyvo',
                'description' => 'Nieuwe digitale ervaringen.',
                'indexable' => '1',
            ],
        ])->assertRedirect();

        $page->refresh();
        self::assertNotNull($page->draft_revision_id);
        self::assertSame(2, PageRevision::query()->count());
        self::assertSame('Pagina Beheerder', PageRevision::query()->findOrFail($page->draft_revision_id)->updated_by_name);
        $this->post("/deyvo/pages/{$page->id}/revisions/{$page->published_revision_id}/restore")->assertRedirect();
        self::assertSame(3, PageRevision::query()->count());
    }

    public function test_preview_markers_and_autosave_keep_published_content_intact(): void
    {
        Route::post('/logout', static fn () => response('Uitgelogd'))->name('logout');
        Route::getRoutes()->refreshNameLookups();
        self::assertTrue(Route::has('logout'));

        $this->actingAs(new GenericUser([
            'id' => 9,
            'name' => 'Editor Beheerder',
            'email' => 'editor@deyvo.test',
        ]));

        app(DashboardManager::class)->registerSchema(json_encode([
            'pages' => [],
            'templates' => [
                [
                    'key' => 'landing',
                    'label' => 'Landingspagina',
                    'sections' => [
                        [
                            'key' => 'hero',
                            'label' => 'Hero',
                            'fields' => [
                                [
                                    'key' => 'title',
                                    'label' => 'Titel',
                                    'type' => 'text',
                                    'required' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        $pages = app(PageManager::class);
        $page = $pages->create([
            'title' => 'Homepage',
            'slug' => 'home',
            'template' => 'landing',
            'sections' => [
                'hero' => [
                    'title' => 'Live titel',
                ],
            ],
            'seo' => [
                'title' => null,
                'description' => null,
                'indexable' => true,
            ],
        ]);
        $pages->publish($page);
        $page->refresh();

        $this->get("/deyvo/pages/{$page->id}/preview")->assertRedirect('http://localhost/home');
        $this->blade('@deyvoEditable(\'home.hero.title\') @deyvoEditor')
            ->assertSee('data-deyvo-field="home.hero.title"', false)
            ->assertSee('data-deyvo-path="hero.title"', false)
            ->assertSee('data-deyvo-value="Live titel"', false)
            ->assertSee('Live titel')
            ->assertSee('data-deyvo-core-styles="enabled"', false)
            ->assertSee('data-deyvo-editor', false)
            ->assertSee('data-deyvo-editor-overlay', false)
            ->assertSee('Editor actief')
            ->assertSee('Ingelogd: Editor Beheerder')
            ->assertSee('data-deyvo-editor-logout', false)
            ->assertSee('Uitloggen')
            ->assertSee('Concept v1');
        $this->patchJson("/deyvo/pages/{$page->id}/fields", [
            'field' => 'hero.title',
            'value' => 'Concepttitel',
        ])->assertOk()->assertJsonPath('value', 'Concepttitel');

        $page->refresh();
        self::assertNotSame($page->published_revision_id, $page->draft_revision_id);
        self::assertSame('Live titel', PageRevision::query()->findOrFail($page->published_revision_id)->sections['hero']['title']);
        self::assertSame('Concepttitel', PageRevision::query()->findOrFail($page->draft_revision_id)->sections['hero']['title']);
        $this->blade('@deyvoEditable(\'home.hero.title\')')
            ->assertSee('Concepttitel');

        $this->patchJson("/deyvo/pages/{$page->id}/fields", [
            'field' => 'hero.unknown',
            'value' => 'Mislukt',
        ])->assertUnprocessable();

        self::assertTrue(AuditLog::query()->where('event', 'page.field_update_failed')->exists());

        $this->post("/deyvo/pages/{$page->id}/preview/stop")
            ->assertRedirect('http://localhost/home');
    }

    public function test_core_interface_styles_can_be_disabled_by_the_consuming_website(): void
    {
        config()->set('deyvo-core.ui.styles.enabled', false);

        $this->view('deyvo::layouts.app', ['slot' => 'Inhoud'])
            ->assertSee('data-deyvo-core-styles="disabled"', false)
            ->assertSee('Inhoud');
    }

    public function test_core_interface_styles_are_enabled_by_default(): void
    {
        $this->view('deyvo::layouts.app', ['slot' => 'Inhoud'])
            ->assertSee('data-deyvo-core-styles="enabled"', false)
            ->assertSee('Inhoud');
    }

    public function test_page_builder_stores_publishes_and_renders_configured_blocks(): void
    {
        app(DashboardManager::class)->registerSchema(json_encode([
            'pages' => [],
            'blocks' => [
                [
                    'key' => 'hero',
                    'label' => 'Hero',
                    'category' => 'Introductie',
                    'fields' => [
                        [
                            'key' => 'heading',
                            'label' => 'Titel',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'key' => 'body',
                            'label' => 'Introductie',
                            'type' => 'textarea',
                        ],
                    ],
                ],
                [
                    'key' => 'text',
                    'label' => 'Tekst',
                    'category' => 'Inhoud',
                    'fields' => [
                        [
                            'key' => 'body',
                            'label' => 'Tekst',
                            'type' => 'textarea',
                            'required' => true,
                        ],
                    ],
                ],
            ],
            'templates' => [
                [
                    'key' => 'builder',
                    'label' => 'Builderpagina',
                    'builder' => [
                        'blocks' => ['hero', 'text'],
                    ],
                    'sections' => [],
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        $this->get('/deyvo/pages/create?template=builder')
            ->assertOk()
            ->assertSee('Blokken')
            ->assertSee('data-deyvo-builder', false);

        $this->post('/deyvo/pages', [
            'title' => 'Over Deyvo',
            'slug' => 'over-deyvo',
            'template' => 'builder',
            'blocks' => json_encode([
                [
                    'id' => 'block-hero',
                    'type' => 'hero',
                    'data' => [
                        'heading' => 'Bouw met blokken',
                        'body' => 'Maak elke pagina op maat.',
                    ],
                ],
                [
                    'id' => 'block-text',
                    'type' => 'text',
                    'data' => [
                        'body' => 'Deyvo Core bewaart ieder concept als revisie.',
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
            'seo' => [
                'indexable' => '1',
            ],
        ])->assertRedirect();

        $page = Page::query()->firstOrFail();
        $revision = $page->draftRevision()->firstOrFail();

        self::assertSame('hero', $revision->blocks[0]['type']);
        self::assertSame('Bouw met blokken', $revision->blocks[0]['data']['heading']);
        self::assertSame('text', $revision->blocks[1]['type']);

        app(PageManager::class)->publish($page);

        $this->blade('<x-deyvo::blocks page="over-deyvo" />')
            ->assertSee('Bouw met blokken')
            ->assertSee('Deyvo Core bewaart ieder concept als revisie.');
    }
}
