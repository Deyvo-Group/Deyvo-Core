<?php

declare(strict_types=1);

namespace Deyvo\Core\Pages;

use Deyvo\Core\Dashboard\DashboardManager;
use Deyvo\Core\Models\Page;
use Deyvo\Core\Models\PageRevision;
use Illuminate\Support\HtmlString;
use InvalidArgumentException;

final class PageContent
{
    public function __construct(
        private PagePreviewState $preview,
        private DashboardManager $dashboard,
    ) {
    }

    public function value(string $key, mixed $default = null): mixed
    {
        [$pageKey, $sectionKey, $fieldKey] = $this->fieldPath($key);
        $revision = $this->preview->revision($pageKey) ?? $this->publishedRevision($pageKey);

        if (! $revision instanceof PageRevision) {
            return $default;
        }

        return data_get($revision->sections, "{$sectionKey}.{$fieldKey}", $default);
    }

    public function editable(string $key, mixed $default = null): HtmlString
    {
        [$pageKey, $sectionKey, $fieldKey] = $this->fieldPath($key);
        $revision = $this->preview->revision($pageKey);
        $value = $this->value($key, $default);

        if (! $revision instanceof PageRevision) {
            return new HtmlString(e($this->stringValue($value)));
        }

        $template = $this->dashboard->pageTemplate($revision->template);
        $field = is_array($template) ? $this->field($template, $sectionKey, $fieldKey) : null;

        if ($field === null) {
            return new HtmlString(e($this->stringValue($value)));
        }

        $attributes = [
            'data-deyvo-field' => $key,
            'data-deyvo-type' => $field['type'],
            'data-deyvo-url' => route('deyvo.dashboard.pages.fields.update', ['page' => $revision->page_id]),
            'data-deyvo-options' => json_encode($field['options'], JSON_THROW_ON_ERROR),
            'class' => 'cursor-pointer rounded-sm outline outline-1 outline-sky-400/60 outline-offset-2',
            'title' => 'Bewerken',
        ];
        $html = '<span';

        foreach ($attributes as $name => $attribute) {
            $html .= ' '.$name.'="'.e($attribute).'"';
        }

        return new HtmlString($html.'>'.e($this->stringValue($value)).'</span>');
    }

    public function blocks(string $pageKey): array
    {
        $revision = $this->preview->revision($pageKey) ?? $this->publishedRevision($pageKey);

        return is_array($revision?->blocks) ? $revision->blocks : [];
    }

    public function editor(): HtmlString
    {
        return new HtmlString('<aside data-deyvo-editor hidden></aside>');
    }

    private function publishedRevision(string $pageKey): ?PageRevision
    {
        $page = Page::query()
            ->where('key', $pageKey)
            ->with('publishedRevision')
            ->first();

        return $page?->publishedRevision;
    }

    private function fieldPath(string $key): array
    {
        $parts = explode('.', $key, 3);

        if (count($parts) !== 3 || in_array('', $parts, true)) {
            throw new InvalidArgumentException("Deyvo page field [{$key}] is invalid.");
        }

        return $parts;
    }

    private function field(array $template, string $sectionKey, string $fieldKey): ?array
    {
        foreach ($template['sections'] as $section) {
            if ($section['key'] !== $sectionKey) {
                continue;
            }

            foreach ($section['fields'] as $field) {
                if ($field['key'] === $fieldKey) {
                    return $field;
                }
            }
        }

        return null;
    }

    private function stringValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return is_scalar($value) ? (string) $value : '';
    }
}
