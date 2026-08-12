<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Controllers\Dashboard;

use Deyvo\Core\Models\Setting;
use Deyvo\Core\Support\AuditLogger;
use Deyvo\Core\Support\Flash;
use Deyvo\Core\Support\SiteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use JsonException;

final class SettingController
{
    public function __construct(
        private AuditLogger $audit,
    ) {
    }

    public function index(): View
    {
        return view('deyvo::dashboard.settings.index', [
            'settings' => Setting::query()->orderBy('group')->orderBy('key')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('deyvo::dashboard.settings.create', [
            'types' => $this->types(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $setting = Setting::query()->create($this->validated($request));
        $this->audit->record('setting.created', $setting, [
            'key' => $setting->key,
        ]);
        Flash::success('Instelling is opgeslagen.');

        return redirect()->route('deyvo.dashboard.settings.index');
    }

    public function edit(Setting $setting): View
    {
        return view('deyvo::dashboard.settings.edit', [
            'setting' => $setting,
            'types' => $this->types(),
        ]);
    }

    public function update(Request $request, Setting $setting): RedirectResponse
    {
        $setting->fill($this->validated($request, $setting));
        $changes = array_keys($setting->getDirty());
        $setting->save();
        $this->audit->record('setting.updated', $setting, [
            'key' => $setting->key,
            'changes' => $changes,
        ]);
        Flash::success('Instelling is bijgewerkt.');

        return redirect()->route('deyvo.dashboard.settings.index');
    }

    public function destroy(Setting $setting): RedirectResponse
    {
        $setting->delete();
        $this->audit->record('setting.deleted', $setting, [
            'key' => $setting->key,
        ]);
        Flash::success('Instelling is verwijderd.');

        return redirect()->route('deyvo.dashboard.settings.index');
    }

    private function validated(Request $request, ?Setting $setting = null): array
    {
        $key = Rule::unique('deyvo_settings', 'key');

        if ($setting) {
            $key->ignore($setting->getKey());
        }

        $validated = Validator::validate($request->all(), [
            'key' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9][a-z0-9._-]*$/', $key],
            'label' => ['nullable', 'string', 'max:160'],
            'group' => ['nullable', 'string', 'max:80'],
            'type' => ['nullable', 'string', Rule::in($this->types())],
            'value' => ['nullable', 'string', 'max:65535'],
            'options' => ['nullable', 'string', 'max:65535'],
        ]);

        $type = $validated['type'] ?? $setting?->type ?? 'text';
        $value = $type === 'boolean'
            ? SiteSettings::stringValue($request->boolean('value'), 'boolean')
            : ($validated['value'] ?? null);

        if ($type === 'json' && is_string($value) && $value !== '') {
            try {
                json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                throw ValidationException::withMessages([
                    'value' => 'De waarde moet geldige JSON bevatten.',
                ]);
            }
        }

        return [
            'key' => $validated['key'],
            'label' => $validated['label'] ?? null,
            'group' => $validated['group'] ?? 'Algemeen',
            'type' => $type,
            'value' => $value,
            'options' => $this->options($validated['options'] ?? null),
        ];
    }

    private function options(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            $options = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw ValidationException::withMessages([
                'options' => 'Opties moeten geldige JSON bevatten.',
            ]);
        }

        if (! is_array($options)) {
            throw ValidationException::withMessages([
                'options' => 'Opties moeten een JSON-object of lijst zijn.',
            ]);
        }

        return $options;
    }

    private function types(): array
    {
        $types = config('deyvo-core.settings.types', []);

        return is_array($types) ? $types : ['text'];
    }
}
