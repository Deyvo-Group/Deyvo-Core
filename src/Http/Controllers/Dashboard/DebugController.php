<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Controllers\Dashboard;

use Deyvo\Core\Dashboard\DashboardManager;
use Deyvo\Core\Models\Content;
use Deyvo\Core\Models\Media;
use Deyvo\Core\Models\Menu;
use Deyvo\Core\Models\Page;
use Deyvo\Core\Models\PageRevision;
use Deyvo\Core\Models\Setting;
use Deyvo\Core\Support\Actor;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class DebugController
{
    public function __construct(
        private Actor $actor,
        private DashboardManager $dashboard,
    ) {
    }

    public function __invoke(): View
    {
        abort_unless(config('deyvo-core.debug.enabled', false), 404);

        return view('deyvo::dashboard.debug.index', [
            'actor' => $this->actor->current(),
            'cache' => $this->cache(),
            'config' => $this->configuration(),
            'counts' => $this->counts(),
            'database' => $this->database(),
            'legacy' => $this->legacy(),
            'pageDiagnostics' => $this->pageDiagnostics(),
            'requestInfo' => $this->requestInfo(),
            'routes' => $this->routes(),
            'schema' => $this->schema(),
        ]);
    }

    private function cache(): array
    {
        return [
            'configuration_cached' => method_exists(app(), 'configurationIsCached') ? app()->configurationIsCached() : null,
            'routes_cached' => method_exists(app(), 'routesAreCached') ? app()->routesAreCached() : null,
            'environment' => app()->environment(),
            'debug' => (bool) config('app.debug', false),
        ];
    }

    private function configuration(): array
    {
        $dashboardPath = trim((string) config('deyvo-core.dashboard.path', 'deyvo'), '/');

        return [
            'deyvo-core.dashboard.enabled' => config('deyvo-core.dashboard.enabled'),
            'deyvo-core.dashboard.path' => $dashboardPath,
            'deyvo-core.dashboard.middleware' => config('deyvo-core.dashboard.middleware'),
            'deyvo-core.dashboard.pages.enabled' => config('deyvo-core.dashboard.pages.enabled'),
            'deyvo-core.debug.enabled' => config('deyvo-core.debug.enabled'),
            'DEYVO_DEBUG_ENABLED' => env('DEYVO_DEBUG_ENABLED'),
            'DEYVO_DASHBOARD_ENABLED' => env('DEYVO_DASHBOARD_ENABLED'),
            'DEYVO_DASHBOARD_PATH' => env('DEYVO_DASHBOARD_PATH'),
            'DEYVO_PAGES_ENABLED' => env('DEYVO_PAGES_ENABLED'),
            'expected_page_edit_url_for_id_1' => url($dashboardPath.'/pages/1/edit'),
            'route_deyvo.dashboard.pages.edit_exists' => Route::has('deyvo.dashboard.pages.edit'),
            'route_deyvo.dashboard.pages.legacy.edit_exists' => Route::has('deyvo.dashboard.pages.legacy.edit'),
        ];
    }

    private function counts(): array
    {
        return [
            'contents' => $this->count(Content::class),
            'settings' => $this->count(Setting::class),
            'pages' => $this->count(Page::class),
            'page_revisions' => $this->count(PageRevision::class),
            'media' => $this->count(Media::class),
            'menus' => $this->count(Menu::class),
        ];
    }

    private function database(): array
    {
        $tables = [
            'deyvo_contents',
            'deyvo_settings',
            'deyvo_pages',
            'deyvo_page_revisions',
            'deyvo_media',
            'deyvo_menus',
            'deyvo_folders',
            'deyvo_audit_logs',
        ];

        return array_map(
            static fn (string $table): array => [
                'table' => $table,
                'exists' => Schema::hasTable($table),
            ],
            $tables,
        );
    }

    private function legacy(): array
    {
        $tables = ['pages', 'contents', 'settings', 'media', 'folders', 'menus'];
        $legacy = [];

        foreach ($tables as $table) {
            $exists = Schema::hasTable($table);
            $legacy[$table] = [
                'exists' => $exists,
                'count' => $exists ? DB::table($table)->count() : null,
                'sample' => $exists ? DB::table($table)->limit(10)->get()->map(static fn (object $row): array => (array) $row)->all() : [],
            ];
        }

        return $legacy;
    }

    private function pageDiagnostics(): array
    {
        if (! Schema::hasTable('deyvo_pages')) {
            return [
                'sample' => [],
                'orphans' => [],
            ];
        }

        $sample = Page::query()
            ->with(['draftRevision', 'publishedRevision'])
            ->orderBy('id')
            ->limit(15)
            ->get()
            ->map(static fn (Page $page): array => [
                'id' => $page->getKey(),
                'key' => $page->key,
                'published_slug' => $page->published_slug,
                'draft_revision_id' => $page->draft_revision_id,
                'draft_revision_exists' => $page->draftRevision instanceof PageRevision,
                'published_revision_id' => $page->published_revision_id,
                'published_revision_exists' => $page->publishedRevision instanceof PageRevision,
                'revision_count' => $page->revisions()->count(),
                'edit_url' => Route::has('deyvo.dashboard.pages.edit')
                    ? route('deyvo.dashboard.pages.edit', $page)
                    : null,
            ])
            ->all();

        return [
            'sample' => $sample,
            'orphans' => array_values(array_filter(
                $sample,
                static fn (array $page): bool => $page['revision_count'] === 0
                    || ($page['draft_revision_id'] !== null && ! $page['draft_revision_exists'])
                    || ($page['published_revision_id'] !== null && ! $page['published_revision_exists']),
            )),
        ];
    }

    private function requestInfo(): array
    {
        $route = request()->route();

        return [
            'url' => request()->fullUrl(),
            'path' => request()->path(),
            'method' => request()->method(),
            'route_name' => $route instanceof LaravelRoute ? $route->getName() : null,
            'route_uri' => $route instanceof LaravelRoute ? $route->uri() : null,
            'actor' => $this->actor->label(),
        ];
    }

    private function routes(): array
    {
        return collect(Route::getRoutes())
            ->filter(static fn (LaravelRoute $route): bool => str_starts_with((string) $route->getName(), 'deyvo.dashboard.'))
            ->map(static fn (LaravelRoute $route): array => [
                'method' => implode('|', $route->methods()),
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
                'middleware' => $route->gatherMiddleware(),
            ])
            ->sortBy('uri')
            ->values()
            ->all();
    }

    private function schema(): array
    {
        $path = config('deyvo-core.dashboard.schema.path');
        $resolvedPath = is_string($path) && $path !== ''
            ? (str_starts_with($path, DIRECTORY_SEPARATOR) ? $path : base_path($path))
            : null;

        try {
            return [
                'path' => $path,
                'resolved_path' => $resolvedPath,
                'exists' => is_string($resolvedPath) && is_file($resolvedPath),
                'pages' => count($this->dashboard->customPages()),
                'layouts' => count($this->dashboard->layouts()),
                'templates' => count($this->dashboard->pageTemplates()),
                'error' => null,
            ];
        } catch (Throwable $exception) {
            return [
                'path' => $path,
                'resolved_path' => $resolvedPath,
                'exists' => is_string($resolvedPath) && is_file($resolvedPath),
                'pages' => null,
                'layouts' => null,
                'templates' => null,
                'error' => $exception->getMessage(),
            ];
        }
    }

    private function count(string $model): ?int
    {
        try {
            return $model::query()->count();
        } catch (Throwable) {
            return null;
        }
    }
}
