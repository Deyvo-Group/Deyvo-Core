<?php

declare(strict_types=1);

namespace Deyvo\Core\Dashboard;

use Deyvo\Core\Models\Content;
use Deyvo\Core\Models\Setting;
use InvalidArgumentException;

final class DashboardManager
{
    private array $navigation = [];

    private ?DashboardSchema $schema = null;

    public function registerNavigation(string $label, string $route, string $active, int $sort = 100): void
    {
        $this->navigation[] = [
            'label' => $label,
            'route' => $route,
            'active' => $active,
            'sort' => $sort,
        ];
    }

    public function registerSchema(string $json): void
    {
        $this->schema = DashboardSchema::fromJson($json);
    }

    public function navigation(): array
    {
        $navigation = config('deyvo-core.dashboard.navigation', []);
        $navigation = is_array($navigation) ? [...$navigation, ...$this->navigation] : $this->navigation;

        if (config('deyvo-core.dashboard.pages.enabled', false)) {
            $navigation[] = [
                'label' => 'Pagina’s',
                'route' => 'deyvo.dashboard.pages.index',
                'active' => 'deyvo.dashboard.pages.*',
                'sort' => 15,
            ];
        }

        if (config('deyvo-core.audit.enabled', true)) {
            $navigation[] = [
                'label' => 'Activiteit',
                'route' => 'deyvo.dashboard.activity.index',
                'active' => 'deyvo.dashboard.activity.*',
                'sort' => 35,
            ];
        }

        foreach ($this->customPages() as $page) {
            $navigation[] = [
                'label' => $page['label'],
                'route' => 'deyvo.dashboard.custom.show',
                'parameters' => ['page' => $page['key']],
                'active' => 'deyvo.dashboard.custom.*',
                'page' => $page['key'],
                'sort' => $page['sort'],
            ];
        }

        usort($navigation, static fn (array $left, array $right): int => ($left['sort'] ?? 100) <=> ($right['sort'] ?? 100));

        return $navigation;
    }

    public function customPages(): array
    {
        return $this->schema()->pages();
    }

    public function page(string $key): ?array
    {
        return $this->schema()->page($key);
    }

    public function pageTemplates(): array
    {
        return $this->schema()->templates();
    }

    public function pageTemplate(string $key): ?array
    {
        return $this->schema()->template($key);
    }

    public function builderBlocks(array $template): array
    {
        if (! ($template['builder']['enabled'] ?? false)) {
            return [];
        }

        $blocks = [];

        foreach ($template['builder']['blocks'] as $key) {
            $block = $this->schema()->block($key);

            if (is_array($block)) {
                $blocks[] = $block;
            }
        }

        return $blocks;
    }

    public function builderBlock(string $key): ?array
    {
        return $this->schema()->block($key);
    }

    public function values(array $page): array
    {
        $settingKeys = [];
        $contentKeys = [];

        foreach ($page['fields'] as $field) {
            if ($field['storage'] === 'content') {
                $contentKeys[] = $field['key'];
            } else {
                $settingKeys[] = $field['key'];
            }
        }

        $settings = $settingKeys === []
            ? []
            : Setting::query()->whereIn('key', $settingKeys)->pluck('value', 'key')->all();
        $contents = $contentKeys === []
            ? []
            : Content::query()->whereIn('key', $contentKeys)->pluck('body', 'key')->all();
        $values = [];

        foreach ($page['fields'] as $field) {
            $values[$field['key']] = $field['storage'] === 'content'
                ? ($contents[$field['key']] ?? null)
                : ($settings[$field['key']] ?? null);
        }

        return $values;
    }

    private function schema(): DashboardSchema
    {
        if ($this->schema instanceof DashboardSchema) {
            return $this->schema;
        }

        $path = config('deyvo-core.dashboard.schema.path');

        if (! is_string($path) || trim($path) === '') {
            return $this->schema = DashboardSchema::empty();
        }

        $resolvedPath = str_starts_with($path, DIRECTORY_SEPARATOR) ? $path : base_path($path);

        if (! is_file($resolvedPath)) {
            throw new InvalidArgumentException("Deyvo dashboard schema not found at [{$resolvedPath}].");
        }

        $json = file_get_contents($resolvedPath);

        if ($json === false) {
            throw new InvalidArgumentException("Deyvo dashboard schema could not be read at [{$resolvedPath}].");
        }

        return $this->schema = DashboardSchema::fromJson($json);
    }
}
