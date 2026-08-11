<?php

declare(strict_types=1);

use Deyvo\Core\Pages\PageContent;

if (! function_exists('deyvo_content')) {
    function deyvo_content(string $key, mixed $default = null): mixed
    {
        return app(PageContent::class)->value($key, $default);
    }
}
