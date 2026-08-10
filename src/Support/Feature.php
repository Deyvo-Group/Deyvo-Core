<?php

declare(strict_types=1);

namespace Deyvo\Core\Support;

final class Feature
{
    public static function enabled(string $name): bool
    {
        return (bool) config('deyvo-core.features.'.$name, false);
    }
}
