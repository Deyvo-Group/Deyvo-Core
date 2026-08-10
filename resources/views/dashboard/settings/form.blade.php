@php($setting = $setting ?? null)

<form method="POST" action="{{ $action }}" class="mt-8 max-w-3xl space-y-6 border-t border-neutral-300 pt-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-deyvo::form.input name="key" label="Sleutel" :value="$setting?->key" placeholder="contact.email" required />
    <x-deyvo::form.textarea name="value" label="Waarde" :value="$setting?->value" placeholder="Vul een waarde in." />

    <div class="flex flex-wrap items-center gap-3">
        <x-deyvo::button type="submit">{{ $submit }}</x-deyvo::button>
        <a href="{{ route('deyvo.dashboard.settings.index') }}" class="text-sm font-medium text-neutral-600 hover:text-neutral-950">Annuleren</a>
    </div>
</form>
