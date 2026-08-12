@php($setting = $setting ?? null)
@php($types = $types ?? config('deyvo-core.settings.types', ['text']))
@php($selectedType = old('type', $setting?->type ?? 'text'))

<form method="POST" action="{{ $action }}" class="mt-8 max-w-3xl space-y-6 border-t border-neutral-300 pt-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-deyvo::form.input name="key" label="Sleutel" :value="$setting?->key" placeholder="contact.email" required />
    <x-deyvo::form.input name="label" label="Label" :value="$setting?->label" placeholder="E-mailadres" />
    <x-deyvo::form.input name="group" label="Groep" :value="$setting?->group ?? 'Algemeen'" placeholder="Contact" />
    <x-deyvo::form.select name="type" label="Type">
        @foreach ($types as $type)
            <option value="{{ $type }}" @selected($selectedType === $type)>{{ $type }}</option>
        @endforeach
    </x-deyvo::form.select>

    @if ($selectedType === 'boolean')
        <x-deyvo::form.checkbox name="value" label="Ingeschakeld" :checked="$setting?->typedValue(false) ?? false" />
    @else
        <x-deyvo::form.textarea name="value" label="Waarde" :value="$setting?->value" placeholder="Vul een waarde in." />
    @endif

    <x-deyvo::form.textarea name="options" label="Opties JSON" :value="$setting?->options ? json_encode($setting->options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : null" placeholder='[{"value":"live","label":"Live"}]' />

    <div class="flex flex-wrap items-center gap-3">
        <x-deyvo::button type="submit">{{ $submit }}</x-deyvo::button>
        <a href="{{ route('deyvo.dashboard.settings.index') }}" class="text-sm font-medium text-neutral-600 hover:text-neutral-950">Annuleren</a>
    </div>
</form>
