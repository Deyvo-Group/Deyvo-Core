@php($oldBlocks = old('blocks'))
@php($blocks = is_string($oldBlocks) ? json_decode($oldBlocks, true) : ($revision?->blocks ?? []))
@php($blocks = is_array($blocks) && array_is_list($blocks) ? $blocks : [])
@php($mediaItems = \Deyvo\Core\Models\Media::query()->orderBy('name')->limit(200)->get(['id', 'name', 'path', 'url'])->map(fn ($item) => ['id' => (string) $item->id, 'name' => $item->name, 'path' => $item->path, 'url' => $item->url])->all())

<section class="border border-neutral-200 bg-white shadow-sm" data-deyvo-builder>
    <input type="hidden" name="blocks" value="{{ json_encode($blocks, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}" data-deyvo-builder-input>

    <script type="application/json" data-deyvo-builder-blocks>@json($blocks, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>
    <script type="application/json" data-deyvo-builder-types>@json($blockTypes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>
    <script type="application/json" data-deyvo-builder-media>@json($mediaItems, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>

    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-neutral-200 px-5 py-4">
        <div>
            <h2 class="text-lg font-semibold text-neutral-950">Blokken</h2>
            <p class="mt-1 text-sm text-neutral-600" data-deyvo-builder-count></p>
        </div>

        <button type="button" data-deyvo-builder-open class="inline-flex items-center justify-center bg-neutral-950 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-neutral-800">Blok toevoegen</button>
    </div>

    <div class="grid min-h-[32rem] lg:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="bg-neutral-50 p-5 sm:p-7">
            <div class="mx-auto max-w-3xl space-y-3" data-deyvo-builder-list></div>
        </div>

        <aside class="border-t border-neutral-200 bg-white p-5 lg:border-t-0 lg:border-l" data-deyvo-builder-inspector></aside>
    </div>

    <dialog data-deyvo-builder-dialog class="w-[min(42rem,calc(100vw-2rem))] border border-neutral-200 bg-white p-0 text-neutral-950 shadow-2xl backdrop:bg-neutral-950/40">
        <div class="flex items-center justify-between border-b border-neutral-200 px-5 py-4">
            <h2 class="text-lg font-semibold">Blok toevoegen</h2>
            <button type="button" data-deyvo-builder-close aria-label="Sluiten" title="Sluiten" class="inline-flex size-9 items-center justify-center text-xl text-neutral-600 transition hover:bg-neutral-100 hover:text-neutral-950">&times;</button>
        </div>
        <div class="max-h-[70vh] overflow-y-auto p-5" data-deyvo-builder-catalogue></div>
    </dialog>
</section>

@error('blocks')
    <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
@enderror
