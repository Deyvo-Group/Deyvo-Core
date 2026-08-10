<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Controllers\Dashboard;

use Deyvo\Core\Models\Setting;
use Deyvo\Core\Support\Flash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class SettingController
{
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
        Setting::query()->create($this->validated($request));
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
        $setting->update($this->validated($request, $setting));
        Flash::success('Instelling is bijgewerkt.');

        return redirect()->route('deyvo.dashboard.settings.index');
    }

    public function destroy(Setting $setting): RedirectResponse
    {
        $setting->delete();
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
