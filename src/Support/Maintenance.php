<?php

declare(strict_types=1);

namespace Deyvo\Core\Support;

final class Maintenance
{
    public static function active(): bool
    {
        return app()->isDownForMaintenance();
    }
}
