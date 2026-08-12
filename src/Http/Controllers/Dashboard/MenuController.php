<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Controllers\Dashboard;

use Deyvo\Core\Models\Menu;
use Deyvo\Core\Support\AuditLogger;
use Deyvo\Core\Support\Flash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use JsonException;

final class MenuController
{
    public function __construct(
        private AuditLogger $audit,
    ) {
    }

    public function index(): View
    {
        return view('deyvo::dashboard.menus.index', [
            'menus' => Menu::query()->latest('updated_at')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('deyvo::dashboard.menus.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $menu = Menu::query()->create($this->validated($request));
        $this->audit->record('menu.created', $menu, [
            'key' => $menu->key,
            'items' => count($menu->items ?? []),
        ]);
        Flash::success('Menu is opgeslagen.');

        return redirect()->route('deyvo.dashboard.menus.index');
    }

    public function edit(Menu $menu): View
    {
        return view('deyvo::dashboard.menus.edit', [
            'menu' => $menu,
        ]);
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $menu->fill($this->validated($request, $menu));
        $changes = array_keys($menu->getDirty());
        $menu->save();

        $this->audit->record('menu.updated', $menu, [
            'key' => $menu->key,
            'changes' => $changes,
        ]);
        Flash::success('Menu is bijgewerkt.');

        return redirect()->route('deyvo.dashboard.menus.index');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->delete();
        $this->audit->record('menu.deleted', null, [
            'subject_label' => $menu->title,
            'key' => $menu->key,
        ]);
        Flash::success('Menu is verwijderd.');

        return redirect()->route('deyvo.dashboard.menus.index');
    }

    private function validated(Request $request, ?Menu $menu = null): array
    {
        $key = Rule::unique('deyvo_menus', 'key');

        if ($menu instanceof Menu) {
            $key->ignore($menu->getKey());
        }

        $validated = Validator::validate($request->all(), [
            'key' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9][a-z0-9._-]*$/', $key],
            'title' => ['required', 'string', 'max:160'],
            'items' => ['nullable'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'key' => $validated['key'],
            'title' => $validated['title'],
            'items' => $this->items($request->input('items', '[]')),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function items(mixed $value): array
    {
        if (is_string($value)) {
            try {
                $value = json_decode($value === '' ? '[]' : $value, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                throw ValidationException::withMessages([
                    'items' => 'Menu-items moeten geldige JSON bevatten.',
                ]);
            }
        }

        if (! is_array($value) || ! array_is_list($value)) {
            throw ValidationException::withMessages([
                'items' => 'Menu-items moeten een JSON-lijst zijn.',
            ]);
        }

        return array_map(fn (mixed $item): array => $this->item($item), $value);
    }

    private function item(mixed $item): array
    {
        if (! is_array($item) || array_is_list($item)) {
            throw ValidationException::withMessages([
                'items' => 'Elk menu-item moet een JSON-object zijn.',
            ]);
        }

        $validated = Validator::validate($item, [
            'label' => ['required', 'string', 'max:120'],
            'url' => ['nullable', 'string', 'max:2048'],
            'target' => ['nullable', Rule::in(['_self', '_blank'])],
            'children' => ['nullable', 'array'],
        ]);

        $children = $validated['children'] ?? [];

        if (! array_is_list($children)) {
            throw ValidationException::withMessages([
                'items' => 'Subitems moeten een JSON-lijst zijn.',
            ]);
        }

        return [
            'label' => $validated['label'],
            'url' => $validated['url'] ?? null,
            'target' => $validated['target'] ?? '_self',
            'children' => array_map(fn (mixed $child): array => $this->item($child), $children),
        ];
    }
}
