@php($mediaItem = $mediaItem ?? null)

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="mt-8 max-w-3xl space-y-6 border-t border-neutral-300 pt-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-deyvo::form.input name="name" label="Naam" :value="$mediaItem?->name" placeholder="Hero afbeelding" />
    <x-deyvo::form.select name="folder_id" label="Map">
        <option value="">Geen map</option>
        @foreach ($folders as $folder)
            <option value="{{ $folder->id }}" @selected((string) old('folder_id', $mediaItem?->folder_id) === (string) $folder->id)>{{ $folder->path }}</option>
        @endforeach
    </x-deyvo::form.select>
    <x-deyvo::form.input name="file" type="file" label="Bestand" />
    <x-deyvo::form.input name="url" type="url" label="Externe URL" :value="$mediaItem?->url" placeholder="https://example.com/image.jpg" />
    <x-deyvo::form.input name="path" label="Opslagpad" :value="$mediaItem?->path" placeholder="deyvo/image.jpg" />
    <x-deyvo::form.input name="alt" label="Alt-tekst" :value="$mediaItem?->alt" />
    <x-deyvo::form.textarea name="caption" label="Bijschrift" :value="$mediaItem?->caption" />

    <div class="flex flex-wrap items-center gap-3">
        <x-deyvo::button type="submit">{{ $submit }}</x-deyvo::button>
        <a href="{{ route('deyvo.dashboard.media.index') }}" class="text-sm font-medium text-neutral-600 hover:text-neutral-950">Annuleren</a>
    </div>
</form>
