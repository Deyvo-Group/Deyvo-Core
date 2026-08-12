@php($menu = $menu ?? null)
@php($items = old('items', $menu?->items ? json_encode($menu->items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : "[]"))

<form method="POST" action="{{ $action }}" class="mt-8 max-w-3xl space-y-6 border-t border-neutral-300 pt-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-deyvo::form.input name="key" label="Sleutel" :value="$menu?->key" placeholder="header" required />
    <x-deyvo::form.input name="title" label="Titel" :value="$menu?->title" placeholder="Hoofdmenu" required />
    <x-deyvo::form.textarea name="items" label="Items JSON" :value="$items" rows="12" />
    <x-deyvo::form.checkbox name="is_active" label="Actief" :checked="$menu?->is_active ?? true" />

    <div class="rounded-md border border-neutral-200 bg-neutral-50 p-4 text-sm text-neutral-600">
        Gebruik items met label, url, optioneel target en children. Bijvoorbeeld [{"label":"Home","url":"/"}].
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <x-deyvo::button type="submit">{{ $submit }}</x-deyvo::button>
        <a href="{{ route('deyvo.dashboard.menus.index') }}" class="text-sm font-medium text-neutral-600 hover:text-neutral-950">Annuleren</a>
    </div>
</form>
