<?php

declare(strict_types=1);

use Deyvo\Core\Pages\PageContent;
use Deyvo\Core\Support\SiteMedia;
use Deyvo\Core\Support\SiteMenus;
use Deyvo\Core\Support\SiteSeo;
use Deyvo\Core\Support\SiteSettings;

if (! function_exists('deyvo_content')) {
    function deyvo_content(string $key, mixed $default = null): mixed
    {
        return app(PageContent::class)->value($key, $default);
    }
}

if (! function_exists('deyvo_blocks')) {
    function deyvo_blocks(string $pageKey): array
    {
        return app(PageContent::class)->blocks($pageKey);
    }
}

if (! function_exists('deyvo_setting')) {
    function deyvo_setting(string $key, mixed $default = null): mixed
    {
        return SiteSettings::get($key, $default);
    }
}

if (! function_exists('deyvo_menu')) {
    function deyvo_menu(string $key, array $default = []): array
    {
        return SiteMenus::get($key, $default);
    }
}

if (! function_exists('deyvo_media_url')) {
    function deyvo_media_url(string|int|null $idOrPath, ?string $default = null): ?string
    {
        return SiteMedia::url($idOrPath, $default);
    }
}

if (! function_exists('deyvo_seo')) {
    function deyvo_seo(string|\Deyvo\Core\Models\Page|\Deyvo\Core\Models\PageRevision|null $page = null): array
    {
        return SiteSeo::metadata($page);
    }
}
