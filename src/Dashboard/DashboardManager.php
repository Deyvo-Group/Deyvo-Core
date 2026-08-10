<?php

declare(strict_types=1);

namespace Deyvo\Core\Dashboard;

final class DashboardManager
{
    private array $navigation = [];

    public function registerNavigation(string $label, string $route, string $active, int $sort = 100): void
    {
        $this->navigation[] = [
            'label' => $label,
            'route' => $route,
            'active' => $active,
            'sort' => $sort,
        ];
    }

    public function navigation(): array
    {
        $navigation = config('deyvo-core.dashboard.navigation', []);
        $navigation = is_array($navigation) ? [...$navigation, ...$this->navigation] : $this->navigation;

        usort($navigation, static fn (array $left, array $right): int => ($left['sort'] ?? 100) <=> ($right['sort'] ?? 100));

        return $navigation;
    }
}
