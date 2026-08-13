<?php

declare(strict_types=1);

namespace Deyvo\Core\Pages;

use Deyvo\Core\Dashboard\DashboardManager;
use Deyvo\Core\Models\Page;
use Deyvo\Core\Models\PageRevision;
use Deyvo\Core\Support\Actor;
use Deyvo\Core\Support\AuditLogger;
use Deyvo\Core\Support\HtmlSanitizer;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

final class PageManager
{
    private ?Closure $previewUrlResolver = null;

    public function __construct(
        private DashboardManager $dashboard,
        private Actor $actor,
        private AuditLogger $audit,
        private HtmlSanitizer $html,
    ) {
    }

    public function templates(): array
    {
        return $this->dashboard->pageTemplates();
    }

    public function template(string $key): ?array
    {
        return $this->dashboard->pageTemplate($key);
    }

    public function builderBlocks(array $template): array
    {
        return $this->dashboard->builderBlocks($template);
    }

    public function registerPreviewUrlResolver(Closure $resolver): void
    {
        $this->previewUrlResolver = $resolver;
    }

    public function previewUrl(Page $page, PageRevision $revision): string
    {
        if ($this->previewUrlResolver instanceof Closure) {
            return ($this->previewUrlResolver)($page, $revision);
        }

        return url('/'.ltrim($revision->slug, '/'));
    }

    public function create(array $attributes): Page
    {
        return DB::transaction(function () use ($attributes): Page {
            $template = $this->template($attributes['template']);

            if ($template === null) {
                throw new InvalidArgumentException("Deyvo page template [{$attributes['template']}] does not exist.");
            }

            $page = Page::query()->create([
                'key' => $attributes['slug'],
            ]);
            $revision = $page->revisions()->create([
                'version' => 1,
                'title' => $attributes['title'],
                'slug' => $attributes['slug'],
                'template' => $template['key'],
                'sections' => $attributes['sections'],
                'blocks' => $this->normaliseBlocks($attributes['blocks'] ?? [], $template),
                'seo' => $attributes['seo'],
                ...$this->actor->attributes('created_by'),
                ...$this->actor->attributes('updated_by'),
            ]);

            $page->update([
                'draft_revision_id' => $revision->getKey(),
            ]);

            $page = $page->refresh();
            $this->audit->record('page.created', $page, [
                'page_key' => $page->key,
                'revision_id' => $revision->getKey(),
                'revision_version' => $revision->version,
            ]);

            return $page;
        });
    }

    public function draft(Page $page): ?PageRevision
    {
        return $page->draftRevision ?? $page->publishedRevision;
    }

    public function editable(Page $page): PageRevision
    {
        $revision = $this->draft($page);

        if ($revision instanceof PageRevision) {
            return $revision;
        }

        $template = $this->templates()[0] ?? null;

        if (! is_array($template)) {
            throw new InvalidArgumentException('Deyvo cannot edit this page because no page templates are configured.');
        }

        return DB::transaction(function () use ($page, $template): PageRevision {
            $revision = $page->revisions()->create([
                'version' => $this->nextVersion($page),
                'title' => $this->fallbackTitle($page),
                'slug' => $this->fallbackSlug($page),
                'template' => $template['key'],
                'sections' => $this->emptySections($template),
                'blocks' => [],
                'seo' => [
                    'title' => null,
                    'description' => null,
                    'indexable' => true,
                ],
                ...$this->actor->attributes('created_by'),
                ...$this->actor->attributes('updated_by'),
            ]);

            $page->update([
                'draft_revision_id' => $revision->getKey(),
            ]);

            $this->audit->record('page.repaired_empty_revision', $page, [
                'page_key' => $page->key,
                'revision_id' => $revision->getKey(),
                'revision_version' => $revision->version,
                'template' => $template['key'],
            ]);

            return $revision;
        });
    }

    public function updateDraft(Page $page, array $attributes): PageRevision
    {
        return DB::transaction(function () use ($page, $attributes): PageRevision {
            $revision = $this->ensureDraft($page);
            $template = $this->template($attributes['template'] ?? $revision->template);

            if ($template === null) {
                throw new InvalidArgumentException("Deyvo page template [{$revision->template}] does not exist.");
            }

            $attributes['blocks'] = $this->normaliseBlocks($attributes['blocks'] ?? $revision->blocks ?? [], $template);
            $revision->fill([
                ...$attributes,
                ...$this->actor->attributes('updated_by'),
            ]);
            $changes = array_values(array_diff(array_keys($revision->getDirty()), [
                'updated_by_id',
                'updated_by_name',
                'updated_by_email',
            ]));
            $revision->save();
            $page->touch();

            $this->audit->record('page.updated', $page, [
                'page_key' => $page->key,
                'revision_id' => $revision->getKey(),
                'revision_version' => $revision->version,
                'changes' => $changes,
            ]);

            return $revision->refresh();
        });
    }

    public function publish(Page $page): PageRevision
    {
        return DB::transaction(function () use ($page): PageRevision {
            $revision = $this->ensureDraft($page);
            $conflict = Page::query()
                ->where('published_slug', $revision->slug)
                ->whereKeyNot($page->getKey())
                ->exists();

            if ($conflict) {
                throw new InvalidArgumentException("Deyvo page slug [{$revision->slug}] is already published.");
            }

            $page->update([
                'published_slug' => $revision->slug,
                'published_revision_id' => $revision->getKey(),
                'draft_revision_id' => null,
            ]);
            $revision->update($this->actor->attributes('updated_by'));

            $this->audit->record('page.published', $page, [
                'page_key' => $page->key,
                'revision_id' => $revision->getKey(),
                'revision_version' => $revision->version,
                'slug' => $revision->slug,
            ]);

            return $revision->refresh();
        });
    }

    public function restore(Page $page, PageRevision $revision): PageRevision
    {
        if ($revision->page_id !== $page->getKey()) {
            throw new InvalidArgumentException('Deyvo page revision does not belong to the page.');
        }

        return DB::transaction(function () use ($page, $revision): PageRevision {
            $draft = $page->revisions()->create([
                'version' => $this->nextVersion($page),
                'title' => $revision->title,
                'slug' => $revision->slug,
                'template' => $revision->template,
                'sections' => $revision->sections,
                'blocks' => $revision->blocks ?? [],
                'seo' => $revision->seo,
                ...$this->actor->attributes('created_by'),
                ...$this->actor->attributes('updated_by'),
            ]);

            $page->update([
                'draft_revision_id' => $draft->getKey(),
            ]);

            $this->audit->record('page.restored', $page, [
                'page_key' => $page->key,
                'source_revision_id' => $revision->getKey(),
                'source_revision_version' => $revision->version,
                'revision_id' => $draft->getKey(),
                'revision_version' => $draft->version,
            ]);

            return $draft;
        });
    }

    public function updateField(Page $page, string $path, mixed $value): array
    {
        return DB::transaction(function () use ($page, $path, $value): array {
            $revision = $this->ensureDraft($page);
            [$sectionKey, $fieldKey] = $this->fieldPath($path);
            $template = $this->template($revision->template);

            if ($template === null) {
                throw new InvalidArgumentException("Deyvo page template [{$revision->template}] does not exist.");
            }

            $field = $this->field($template, $sectionKey, $fieldKey);

            if ($field === null) {
                throw new InvalidArgumentException("Deyvo page field [{$path}] does not exist.");
            }

            $validated = Validator::validate(['value' => $value], [
                'value' => $this->rules($field),
            ]);
            $sections = $revision->sections;
            $sections[$sectionKey][$fieldKey] = match ($field['type']) {
                'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                'html' => $this->html->clean($validated['value'] ?? null),
                default => $validated['value'] ?? null,
            };
            $revision->update([
                'sections' => $sections,
                ...$this->actor->attributes('updated_by'),
            ]);
            $page->touch();

            $this->audit->record('page.field_updated', $page, [
                'page_key' => $page->key,
                'revision_id' => $revision->getKey(),
                'revision_version' => $revision->version,
                'field' => $path,
                'type' => $field['type'],
            ]);

            return [
                'revision' => $revision->refresh(),
                'field' => $field,
                'value' => $sections[$sectionKey][$fieldKey],
            ];
        });
    }

    private function ensureDraft(Page $page): PageRevision
    {
        if ($page->draftRevision instanceof PageRevision) {
            return $page->draftRevision;
        }

        if (! ($page->publishedRevision instanceof PageRevision)) {
            throw new InvalidArgumentException("Deyvo page [{$page->key}] has no editable revision.");
        }

        $published = $page->publishedRevision;
        $draft = $page->revisions()->create([
            'version' => $this->nextVersion($page),
            'title' => $published->title,
            'slug' => $published->slug,
            'template' => $published->template,
            'sections' => $published->sections,
            'blocks' => $published->blocks ?? [],
            'seo' => $published->seo,
            ...$this->actor->attributes('created_by'),
            ...$this->actor->attributes('updated_by'),
        ]);

        $page->update([
            'draft_revision_id' => $draft->getKey(),
        ]);

        return $draft;
    }

    private function nextVersion(Page $page): int
    {
        return ((int) $page->revisions()->max('version')) + 1;
    }

    private function emptySections(array $template): array
    {
        $sections = [];

        foreach ($template['sections'] as $section) {
            foreach ($section['fields'] as $field) {
                $sections[$section['key']][$field['key']] = $field['type'] === 'boolean' ? false : null;
            }
        }

        return $sections;
    }

    private function fallbackSlug(Page $page): string
    {
        $slug = $page->published_slug ?: $page->key ?: 'page-'.$page->getKey();
        $slug = trim((string) $slug, '/');

        return preg_match('/^[a-z0-9][a-z0-9-]*$/', $slug) === 1 ? $slug : 'page-'.$page->getKey();
    }

    private function fallbackTitle(Page $page): string
    {
        $slug = $this->fallbackSlug($page);

        return str($slug)->replace('-', ' ')->headline()->toString();
    }

    private function fieldPath(string $path): array
    {
        $parts = explode('.', $path, 2);

        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            throw new InvalidArgumentException("Deyvo page field [{$path}] is invalid.");
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

    private function normaliseBlocks(mixed $blocks, array $template): array
    {
        if (! ($template['builder']['enabled'] ?? false)) {
            return [];
        }

        if (! is_array($blocks) || ! array_is_list($blocks)) {
            throw new InvalidArgumentException('Deyvo page blocks must be a JSON array.');
        }

        $allowedBlocks = [];

        foreach ($this->builderBlocks($template) as $block) {
            $allowedBlocks[$block['key']] = $block;
        }

        $normalised = [];
        $ids = [];

        foreach ($blocks as $block) {
            if (! is_array($block) || array_is_list($block)) {
                throw new InvalidArgumentException('Every Deyvo page block must be a JSON object.');
            }

            $id = $block['id'] ?? null;

            if (! is_string($id) || preg_match('/^[a-z0-9][a-z0-9-]{0,79}$/', $id) !== 1) {
                throw new InvalidArgumentException('Deyvo page block id is invalid.');
            }

            if (in_array($id, $ids, true)) {
                throw new InvalidArgumentException("Deyvo page block id [{$id}] is duplicated.");
            }

            $type = $block['type'] ?? null;

            if (! is_string($type) || ! isset($allowedBlocks[$type])) {
                throw new InvalidArgumentException('Deyvo page block type is not allowed by the template.');
            }

            $data = $block['data'] ?? [];

            if (! is_array($data) || array_is_list($data)) {
                throw new InvalidArgumentException("Deyvo page block [{$id}] data must be a JSON object.");
            }

            $definition = $allowedBlocks[$type];
            $fields = array_column($definition['fields'], 'key');
            $unknownFields = array_diff(array_keys($data), $fields);

            if ($unknownFields !== []) {
                throw new InvalidArgumentException("Deyvo page block [{$id}] contains an unknown field.");
            }

            $values = [];

            foreach ($definition['fields'] as $field) {
                $value = $data[$field['key']] ?? null;
                $validated = Validator::validate(['value' => $value], [
                    'value' => $this->rules($field),
                ]);
                $values[$field['key']] = match ($field['type']) {
                    'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                    'html' => $this->html->clean($validated['value'] ?? null),
                    default => $validated['value'] ?? null,
                };
            }

            $ids[] = $id;
            $normalised[] = [
                'id' => $id,
                'type' => $type,
                'data' => $values,
            ];
        }

        return $normalised;
    }

    private function rules(array $field): array
    {
        $rules = [$field['required'] ? 'required' : 'nullable'];

        if ($field['type'] === 'boolean') {
            $rules[] = 'boolean';

            return $rules;
        }

        $rules[] = 'string';

        if ($field['type'] === 'email') {
            $rules[] = 'email';
            $rules[] = 'max:255';

            return $rules;
        }

        if ($field['type'] === 'url') {
            $rules[] = 'url';
            $rules[] = 'max:2048';

            return $rules;
        }

        if ($field['type'] === 'media') {
            $rules[] = 'max:500';

            return $rules;
        }

        if ($field['type'] === 'select') {
            $rules[] = Rule::in(array_column($field['options'], 'value'));

            return $rules;
        }

        $rules[] = $field['type'] === 'html' ? 'max:100000' : 'max:65535';

        return $rules;
    }
}
