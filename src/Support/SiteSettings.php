<?php

declare(strict_types=1);

namespace Deyvo\Core\Support;

use Deyvo\Core\Models\Setting;

final class SiteSettings
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return Setting::query()
            ->where('key', $key)
            ->value('value') ?? $default;
    }
}
