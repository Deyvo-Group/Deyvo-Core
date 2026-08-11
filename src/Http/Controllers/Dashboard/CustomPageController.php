<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Controllers\Dashboard;

use Deyvo\Core\Dashboard\DashboardManager;
use Deyvo\Core\Models\Content;
use Deyvo\Core\Models\Setting;
use Deyvo\Core\Support\Flash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class CustomPageController
{
    public function __construct(
        private DashboardManager $dashboard,
    ) {
    }

    public function show(string $page): View
    {
        $definition = $this->page($page);

        return view('deyvo::dashboard.custom.show', [
            'page' => $definition,
            'values' => $this->dashboard->values($definition),
        ]);
    }

    public function update(Request $request, string $page): RedirectResponse
    {
        $definition = $this->page($page);
        $values = $this->validated($request, $definition);

        foreach ($definition['fields'] as $index => $field) {
            $value = $field['type'] === 'boolean'
                ? $request->boolean("values.{$index}")
                : ($values[$index] ?? null);

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

        Flash::success('Instellingen zijn bijgewerkt.');

        return redirect()->route('deyvo.dashboard.custom.show', ['page' => $definition['key']]);
    }

    private function page(string $key): array
    {
        $page = $this->dashboard->page($key);

        abort_unless($page !== null, 404);

        return $page;
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

        $rules[] = 'max:65535';

        return $rules;
    }
}
