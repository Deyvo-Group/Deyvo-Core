<?php

declare(strict_types=1);

namespace Deyvo\Core\Console\Commands;

use Deyvo\Core\Models\Content;
use Deyvo\Core\Models\Folder;
use Deyvo\Core\Models\Media;
use Deyvo\Core\Models\Menu;
use Deyvo\Core\Models\Page;
use Deyvo\Core\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class ImportLegacyCmsCommand extends Command
{
    protected $signature = 'deyvo:import-legacy-cms {--dry-run : Report what would be imported without writing}';

    protected $description = 'Import legacy host-app CMS tables into Deyvo Core-owned tables.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $counts = [
            'settings' => $this->importSettings($dryRun),
            'contents' => $this->importContents($dryRun),
            'folders' => $this->importFolders($dryRun),
            'media' => $this->importMedia($dryRun),
            'menus' => $this->importMenus($dryRun),
            'pages' => $this->importPages($dryRun),
        ];

        foreach ($counts as $type => $count) {
            $this->line("{$type}: {$count}");
        }

        $this->components->info($dryRun ? 'Legacy CMS import dry-run complete.' : 'Legacy CMS import complete.');

        return self::SUCCESS;
    }

    private function importSettings(bool $dryRun): int
    {
        if (! Schema::hasTable('settings')) {
            return 0;
        }

        $count = 0;

        DB::table('settings')->orderBy('id')->each(function (object $row) use (&$count, $dryRun): void {
            $key = $this->string($row, ['key', 'name']);

            if ($key === null) {
                return;
            }

            $count++;

            if ($dryRun) {
                return;
            }

            Setting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'label' => $this->string($row, ['label', 'title']) ?? Str::headline(str_replace('.', ' ', $key)),
                    'group' => $this->string($row, ['group', 'section']) ?? 'Legacy',
                    'type' => $this->string($row, ['type']) ?? 'text',
                    'value' => $this->string($row, ['value']),
                ],
            );
        });

        return $count;
    }

    private function importContents(bool $dryRun): int
    {
        if (! Schema::hasTable('contents')) {
            return 0;
        }

        $count = 0;

        DB::table('contents')->orderBy('id')->each(function (object $row) use (&$count, $dryRun): void {
            $key = $this->string($row, ['key', 'slug']);

            if ($key === null) {
                return;
            }

            $count++;

            if ($dryRun) {
                return;
            }

            Content::query()->updateOrCreate(
                ['key' => $key],
                [
                    'title' => $this->string($row, ['title', 'name']) ?? Str::headline(str_replace('.', ' ', $key)),
                    'body' => $this->string($row, ['body', 'content', 'value']),
                    'is_published' => $this->boolean($row, ['is_published', 'published', 'active'], true),
                ],
            );
        });

        return $count;
    }

    private function importFolders(bool $dryRun): int
    {
        if (! Schema::hasTable('folders')) {
            return 0;
        }

        $count = 0;

        DB::table('folders')->orderBy('id')->each(function (object $row) use (&$count, $dryRun): void {
            $name = $this->string($row, ['name', 'title']);

            if ($name === null) {
                return;
            }

            $slug = $this->string($row, ['slug']) ?? Str::slug($name);
            $path = $this->string($row, ['path']) ?? $slug;
            $count++;

            if ($dryRun) {
                return;
            }

            Folder::query()->updateOrCreate(
                ['path' => $path],
                [
                    'parent_id' => $this->integer($row, ['parent_id']),
                    'name' => $name,
                    'slug' => $slug,
                ],
            );
        });

        return $count;
    }

    private function importMedia(bool $dryRun): int
    {
        if (! Schema::hasTable('media')) {
            return 0;
        }

        $count = 0;

        DB::table('media')->orderBy('id')->each(function (object $row) use (&$count, $dryRun): void {
            $path = $this->string($row, ['path', 'file', 'filename']);
            $url = $this->string($row, ['url']);

            if ($path === null && $url === null) {
                return;
            }

            $count++;

            if ($dryRun) {
                return;
            }

            Media::query()->updateOrCreate(
                $path !== null ? ['path' => $path] : ['url' => $url],
                [
                    'folder_id' => $this->integer($row, ['folder_id']),
                    'name' => $this->string($row, ['name', 'title', 'alt']) ?? basename((string) ($path ?? $url)),
                    'disk' => $this->string($row, ['disk']) ?? config('deyvo-core.dashboard.media.disk', 'public'),
                    'url' => $url,
                    'mime_type' => $this->string($row, ['mime_type', 'type']),
                    'size' => $this->integer($row, ['size']),
                    'alt' => $this->string($row, ['alt', 'alt_text']),
                    'caption' => $this->string($row, ['caption', 'description']),
                ],
            );
        });

        return $count;
    }

    private function importMenus(bool $dryRun): int
    {
        if (! Schema::hasTable('menus')) {
            return 0;
        }

        $count = 0;

        DB::table('menus')->orderBy('id')->each(function (object $row) use (&$count, $dryRun): void {
            $key = $this->string($row, ['key', 'slug', 'name']);

            if ($key === null) {
                return;
            }

            $count++;

            if ($dryRun) {
                return;
            }

            Menu::query()->updateOrCreate(
                ['key' => $key],
                [
                    'title' => $this->string($row, ['title', 'label', 'name']) ?? Str::headline($key),
                    'items' => $this->json($row, ['items', 'structure']) ?? [],
                    'is_active' => $this->boolean($row, ['is_active', 'active', 'published'], true),
                ],
            );
        });

        return $count;
    }

    private function importPages(bool $dryRun): int
    {
        if (! Schema::hasTable('pages')) {
            return 0;
        }

        $count = 0;

        DB::table('pages')->orderBy('id')->each(function (object $row) use (&$count, $dryRun): void {
            $slug = $this->string($row, ['slug', 'key']);

            if ($slug === null) {
                return;
            }

            $count++;

            if ($dryRun) {
                return;
            }

            $page = Page::query()->firstOrCreate(['key' => $slug]);
            $revision = $page->revisions()->create([
                'version' => ((int) $page->revisions()->max('version')) + 1,
                'title' => $this->string($row, ['title', 'name']) ?? Str::headline($slug),
                'slug' => $slug,
                'template' => $this->string($row, ['template', 'key']) ?? 'standard-page',
                'sections' => $this->json($row, ['sections', 'fields']) ?? [],
                'blocks' => $this->json($row, ['blocks']) ?? [],
                'seo' => [
                    'title' => $this->string($row, ['seo_title', 'meta_title']),
                    'description' => $this->string($row, ['seo_description', 'meta_description']),
                    'indexable' => $this->boolean($row, ['seo_indexable', 'indexable'], true),
                ],
            ]);

            $page->update([
                'published_slug' => $this->boolean($row, ['is_published', 'published', 'active'], true) ? $slug : $page->published_slug,
                'published_revision_id' => $this->boolean($row, ['is_published', 'published', 'active'], true) ? $revision->getKey() : $page->published_revision_id,
                'draft_revision_id' => $this->boolean($row, ['is_published', 'published', 'active'], true) ? null : $revision->getKey(),
            ]);
        });

        return $count;
    }

    private function string(object $row, array $columns): ?string
    {
        foreach ($columns as $column) {
            if (! property_exists($row, $column) || $row->{$column} === null) {
                continue;
            }

            $value = trim((string) $row->{$column});

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function integer(object $row, array $columns): ?int
    {
        $value = $this->string($row, $columns);

        return $value === null ? null : (int) $value;
    }

    private function boolean(object $row, array $columns, bool $default): bool
    {
        $value = $this->string($row, $columns);

        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function json(object $row, array $columns): ?array
    {
        $value = $this->string($row, $columns);

        if ($value === null) {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }
}
