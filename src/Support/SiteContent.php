<?php

declare(strict_types=1);

namespace Deyvo\Core\Support;

use Deyvo\Core\Models\Content;

final class SiteContent
{
    public static function body(string $key, ?string $default = null): ?string
    {
        return Content::query()
            ->published()
            ->where('key', $key)
            ->value('body') ?? $default;
    }
}
