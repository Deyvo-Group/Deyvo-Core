@php($content = $content ?? null)

<form method="POST" action="{{ $action }}" class="mt-8 max-w-3xl space-y-6 border-t border-neutral-300 pt-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-deyvo::form.input name="key" label="Sleutel" :value="$content?->key" placeholder="homepage.intro" required />
    <x-deyvo::form.input name="title" label="Titel" :value="$content?->title" required />
    <x-deyvo::form.textarea name="body" label="Inhoud" :value="$content?->body" placeholder="Schrijf de inhoud van dit blok." />
    <x-deyvo::form.checkbox name="is_published" label="Publiceren" :checked="$content?->is_published ?? false" />

    <div class="flex flex-wrap items-center gap-3">
        <x-deyvo::button type="submit">{{ $submit }}</x-deyvo::button>
        <a href="{{ route('deyvo.dashboard.contents.index') }}" class="text-sm font-medium text-neutral-600 hover:text-neutral-950">Annuleren</a>
    </div>
</form>
