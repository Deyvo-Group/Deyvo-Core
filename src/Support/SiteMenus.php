<?php

declare(strict_types=1);

namespace Deyvo\Core\Support;

use Deyvo\Core\Models\Menu;

final class SiteMenus
{
    public static function get(string $key, array $default = []): array
    {
        $menu = Menu::query()
            ->active()
            ->where('key', $key)
            ->first();

        return $menu instanceof Menu && is_array($menu->items) ? $menu->items : $default;
    }
}
