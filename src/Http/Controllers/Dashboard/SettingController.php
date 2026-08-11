<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Controllers\Dashboard;

use Deyvo\Core\Models\Setting;
use Deyvo\Core\Support\AuditLogger;
use Deyvo\Core\Support\Flash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class SettingController
{
    public function __construct(
        private AuditLogger $audit,
    ) {
    }

    public function index(): View
    {
        return view('deyvo::dashboard.settings.index', [
            'settings' => Setting::query()->orderBy('key')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('deyvo::dashboard.settings.create');
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

        return Validator::validate($request->all(), [
            'key' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9][a-z0-9._-]*$/', $key],
            'value' => ['nullable', 'string', 'max:65535'],
        ]);
    }
}
