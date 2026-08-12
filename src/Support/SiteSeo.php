<?php

declare(strict_types=1);

namespace Deyvo\Core\Support;

use Deyvo\Core\Models\Page;
use Deyvo\Core\Models\PageRevision;
use Deyvo\Core\Pages\PagePreviewState;

final class SiteSeo
{
    public static function metadata(string|Page|PageRevision|null $page = null): array
    {
        $revision = self::revision($page);
        $seo = is_array($revision?->seo) ? $revision->seo : [];
        $title = $seo['title'] ?? SiteSettings::get('seo.title', config('deyvo-core.name'));
        $description = $seo['description'] ?? SiteSettings::get('seo.description');
        $indexable = $seo['indexable'] ?? SiteSettings::get('seo.indexable', true);

        return [
            'title' => $title,
            'description' => $description,
            'indexable' => filter_var($indexable, FILTER_VALIDATE_BOOLEAN),
            'canonical_url' => SiteSettings::get('seo.canonical_url'),
            'image' => SiteSettings::get('seo.og_image'),
            'robots' => filter_var($indexable, FILTER_VALIDATE_BOOLEAN) ? 'index,follow' : 'noindex,nofollow',
        ];
    }

    private static function revision(string|Page|PageRevision|null $page): ?PageRevision
    {
        if ($page instanceof PageRevision) {
            return $page;
        }

        if ($page instanceof Page) {
            return app(PagePreviewState::class)->revision($page->key) ?? $page->publishedRevision;
        }

        if (is_string($page) && $page !== '') {
            $preview = app(PagePreviewState::class)->revision($page);

            if ($preview instanceof PageRevision) {
                return $preview;
            }

            return Page::query()
                ->where('key', $page)
                ->orWhere('published_slug', $page)
                ->with('publishedRevision')
                ->first()
                ?->publishedRevision;
        }

        return null;
    }
}
