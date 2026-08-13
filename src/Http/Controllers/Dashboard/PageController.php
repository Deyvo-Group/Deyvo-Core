<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Controllers\Dashboard;

use Deyvo\Core\Models\Page;
use Deyvo\Core\Models\PageRevision;
use Deyvo\Core\Pages\PageManager;
use Deyvo\Core\Support\AuditLogger;
use Deyvo\Core\Support\Flash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use JsonException;

final class PageController
{
    public function __construct(
        private PageManager $pages,
        private AuditLogger $audit,
    ) {
    }

    public function index(): View
    {
        return view('deyvo::dashboard.pages.index', [
            'pages' => Page::query()
                ->with(['publishedRevision', 'draftRevision'])
                ->latest('updated_at')
                ->paginate(15),
            'hasTemplates' => $this->pages->templates() !== [],
        ]);
    }

    public function create(Request $request): View
    {
        $template = $this->template($request->query('template'));

        return view('deyvo::dashboard.pages.create', [
            'template' => $template,
            'templates' => $this->pages->templates(),
            'blockTypes' => $this->pages->builderBlocks($template),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $this->pageAttributes($request, true);

        try {
            $page = $this->pages->create($attributes);
        } catch (InvalidArgumentException $exception) {
            $this->audit->record('page.create_failed', null, [
                'subject_label' => $attributes['title'],
                'message' => $exception->getMessage(),
            ]);

            return back()->withInput()->withErrors(['blocks' => $exception->getMessage()]);
        }

        Flash::success('Pagina is als concept aangemaakt.');

        return redirect()->route('deyvo.dashboard.pages.edit', $page);
    }

    public function edit(Page $page): View
    {
        try {
            $revision = $this->pages->editable($page);
        } catch (InvalidArgumentException $exception) {
            $this->audit->record('page.edit_failed', $page, [
                'page_key' => $page->key,
                'message' => $exception->getMessage(),
            ]);

            abort(404, $exception->getMessage());
        }

        $template = $this->template($revision->template);

        return view('deyvo::dashboard.pages.edit', [
            'page' => $page,
            'revision' => $revision,
            'template' => $template,
            'templates' => $this->pages->templates(),
            'blockTypes' => $this->pages->builderBlocks($template),
        ]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        try {
            $this->pages->updateDraft($page, $this->pageAttributes($request));
        } catch (InvalidArgumentException $exception) {
            $this->audit->record('page.update_failed', $page, [
                'page_key' => $page->key,
                'message' => $exception->getMessage(),
            ]);

            return back()->withInput()->withErrors(['blocks' => $exception->getMessage()]);
        }

        Flash::success('Concept is opgeslagen.');

        return redirect()->route('deyvo.dashboard.pages.edit', $page);
    }

    public function publish(Page $page): RedirectResponse
    {
        try {
            $this->pages->publish($page);
        } catch (InvalidArgumentException $exception) {
            $this->audit->record('page.publish_failed', $page, [
                'page_key' => $page->key,
                'message' => $exception->getMessage(),
            ]);

            return back()->withErrors(['slug' => $exception->getMessage()]);
        }

        Flash::success('Pagina is gepubliceerd.');

        return redirect()->route('deyvo.dashboard.pages.edit', $page);
    }

    public function revisions(Page $page): View
    {
        return view('deyvo::dashboard.pages.revisions', [
            'page' => $page,
            'revisions' => $page->revisions()->latest('version')->paginate(15),
        ]);
    }

    public function restore(Page $page, PageRevision $revision): RedirectResponse
    {
        $this->pages->restore($page, $revision);
        Flash::success('Revisie is als concept hersteld.');

        return redirect()->route('deyvo.dashboard.pages.edit', $page);
    }

    private function pageAttributes(Request $request, bool $creating = false): array
    {
        $templates = $this->pages->templates();
        $templateKeys = array_column($templates, 'key');
        $rules = [
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:160', 'regex:/^[a-z0-9][a-z0-9-]*$/'],
            'template' => ['required', 'string', Rule::in($templateKeys)],
        ];

        if ($creating) {
            $rules['slug'][] = Rule::unique('deyvo_pages', 'key');
        }

        $attributes = Validator::validate($request->all(), $rules);
        $template = $this->template($attributes['template']);

        return [
            ...$attributes,
            'sections' => $this->sections($request, $template),
            'blocks' => $this->blocks($request),
            'seo' => $this->seo($request),
        ];
    }

    private function blocks(Request $request): array
    {
        $value = $request->input('blocks', '[]');

        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value)) {
            throw ValidationException::withMessages([
                'blocks' => 'De blokken moeten een geldige lijst zijn.',
            ]);
        }

        try {
            $blocks = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw ValidationException::withMessages([
                'blocks' => 'De blokken kunnen niet worden gelezen.',
            ]);
        }

        if (! is_array($blocks) || ! array_is_list($blocks)) {
            throw ValidationException::withMessages([
                'blocks' => 'De blokken moeten een lijst zijn.',
            ]);
        }

        return $blocks;
    }

    private function template(?string $key): array
    {
        $templates = $this->pages->templates();
        $template = $key === null || $key === ''
            ? ($templates[0] ?? null)
            : $this->pages->template($key);

        abort_unless(is_array($template), 404);

        return $template;
    }

    private function sections(Request $request, array $template): array
    {
        $rules = [];

        foreach ($template['sections'] as $section) {
            foreach ($section['fields'] as $field) {
                $rules["sections.{$section['key']}.{$field['key']}"] = $this->rules($field);
            }
        }

        $validated = Validator::validate($request->all(), $rules);
        $sections = [];

        foreach ($template['sections'] as $section) {
            foreach ($section['fields'] as $field) {
                $path = "{$section['key']}.{$field['key']}";
                $sections[$section['key']][$field['key']] = $field['type'] === 'boolean'
                    ? $request->boolean("sections.{$path}")
                    : data_get($validated, "sections.{$path}");
            }
        }

        return $sections;
    }

    private function seo(Request $request): array
    {
        $validated = Validator::validate($request->all(), [
            'seo.title' => ['nullable', 'string', 'max:160'],
            'seo.description' => ['nullable', 'string', 'max:500'],
            'seo.indexable' => ['nullable', 'boolean'],
        ]);

        return [
            'title' => data_get($validated, 'seo.title'),
            'description' => data_get($validated, 'seo.description'),
            'indexable' => $request->boolean('seo.indexable'),
        ];
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

        $rules[] = 'max:65535';

        return $rules;
    }
}
