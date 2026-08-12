<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Controllers\Dashboard;

use Deyvo\Core\Dashboard\DashboardManager;
use Deyvo\Core\Models\Content;
use Deyvo\Core\Models\Setting;
use Deyvo\Core\Support\AuditLogger;
use Deyvo\Core\Support\Flash;
use Deyvo\Core\Support\HtmlSanitizer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class CustomPageController
{
    public function __construct(
        private DashboardManager $dashboard,
        private AuditLogger $audit,
        private HtmlSanitizer $html,
    ) {
    }

    public function show(string $page): View
    {
        $definition = $this->page($page);

        return $this->form($definition, route('deyvo.dashboard.custom.update', ['page' => $page]));
    }

    public function update(Request $request, string $page): RedirectResponse
    {
        $definition = $this->page($page);

        return $this->updateDefinition(
            $request,
            $definition,
            'custom.updated',
            ['page' => $definition['key']],
            route('deyvo.dashboard.custom.show', ['page' => $definition['key']]),
        );
    }

    public function showLayout(string $layout): View
    {
        $definition = $this->layout($layout);

        return $this->form($definition, route('deyvo.dashboard.layouts.update', ['layout' => $layout]));
    }

    public function updateLayout(Request $request, string $layout): RedirectResponse
    {
        $definition = $this->layout($layout);

        return $this->updateDefinition(
            $request,
            $definition,
            'layout.updated',
            ['layout' => $definition['key']],
            route('deyvo.dashboard.layouts.show', ['layout' => $definition['key']]),
        );
    }

    private function form(array $definition, string $updateUrl): View
    {
        return view('deyvo::dashboard.custom.show', [
            'page' => $definition,
            'values' => $this->dashboard->values($definition),
            'updateUrl' => $updateUrl,
        ]);
    }

    private function updateDefinition(Request $request, array $definition, string $event, array $context, string $redirectUrl): RedirectResponse
    {
        $values = $this->validated($request, $definition);
        $fields = [];

        foreach ($definition['fields'] as $index => $field) {
            $fields[] = $field['key'];
            $value = $field['type'] === 'boolean'
                ? $request->boolean("values.{$index}")
                : ($values[$index] ?? null);
            $value = $field['type'] === 'html' ? $this->html->clean($value) : $value;

            if ($field['storage'] === 'content') {
                Content::query()->updateOrCreate(
                    ['key' => $field['key']],
                    [
                        'title' => $field['content_title'] ?? $field['label'],
                        'body' => $value,
                        'is_published' => $field['published'],
                    ],
                );

                continue;
            }

            Setting::query()->updateOrCreate(
                ['key' => $field['key']],
                ['value' => $value],
            );
        }

        $this->audit->record($event, null, [
            'subject_label' => $definition['label'],
            ...$context,
            'fields' => $fields,
        ]);

        Flash::success('Instellingen zijn bijgewerkt.');

        return redirect()->to($redirectUrl);
    }

    private function page(string $key): array
    {
        $page = $this->dashboard->page($key);

        abort_unless($page !== null, 404);

        return $page;
    }

    private function layout(string $key): array
    {
        $layout = $this->dashboard->layout($key);

        abort_unless($layout !== null, 404);

        return $layout;
    }

    private function validated(Request $request, array $page): array
    {
        $rules = [];

        foreach ($page['fields'] as $index => $field) {
            $rules["values.{$index}"] = $this->rules($field);
        }

        $validated = Validator::validate($request->all(), $rules);

        return $validated['values'] ?? [];
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

        if ($field['type'] === 'select') {
            $rules[] = Rule::in(array_column($field['options'], 'value'));

            return $rules;
        }

        $rules[] = $field['type'] === 'html' ? 'max:100000' : 'max:65535';

        return $rules;
    }
}
