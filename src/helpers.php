<?php

declare(strict_types=1);

use Deyvo\Core\Pages\PageContent;

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
