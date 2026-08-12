@component('deyvo::dashboard.layout', ['title' => 'Media'])
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-sky-700">Dashboard</p>
            <h1 class="mt-1 text-2xl font-semibold text-neutral-950">Media</h1>
            <p class="mt-2 text-sm text-neutral-600">Beheer uploads, externe media en mappen vanuit Core.</p>
        </div>

        <a href="{{ route('deyvo.dashboard.media.create') }}" class="inline-flex items-center justify-center rounded-md bg-neutral-950 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-neutral-800">Media toevoegen</a>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-[18rem_minmax(0,1fr)]">
        <aside class="space-y-5">
            <section class="border border-neutral-200 bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-neutral-950">Mappen</h2>
                <div class="mt-3 space-y-1 text-sm">
                    <a href="{{ route('deyvo.dashboard.media.index') }}" class="block rounded-md px-2 py-1.5 {{ $currentFolder ? 'text-neutral-600 hover:bg-neutral-50' : 'bg-neutral-950 text-white' }}">Alle media</a>
                    @foreach ($folders as $folder)
                        <a href="{{ route('deyvo.dashboard.media.index', ['folder' => $folder->id]) }}" class="block rounded-md px-2 py-1.5 {{ $currentFolder?->is($folder) ? 'bg-neutral-950 text-white' : 'text-neutral-600 hover:bg-neutral-50' }}">
                            <span class="block">{{ $folder->name }}</span>
                            <span class="block truncate text-xs opacity-70">{{ $folder->path }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

            <form method="POST" action="{{ route('deyvo.dashboard.media.folders.store') }}" class="space-y-3 border border-neutral-200 bg-white p-4 shadow-sm">
                @csrf
                <h2 class="text-sm font-semibold text-neutral-950">Map toevoegen</h2>
                <x-deyvo::form.input name="name" label="Naam" />
                <x-deyvo::form.select name="parent_id" label="Bovenliggende map">
                    <option value="">Geen</option>
                    @foreach ($folders as $folder)
                        <option value="{{ $folder->id }}">{{ $folder->path }}</option>
                    @endforeach
                </x-deyvo::form.select>
                <x-deyvo::button type="submit" variant="secondary">Map opslaan</x-deyvo::button>
            </form>
        </aside>

        <section>
            @if ($media->isEmpty())
                <x-deyvo::empty-state title="Nog geen media" description="Upload een bestand of registreer een externe URL.">
                    <a href="{{ route('deyvo.dashboard.media.create') }}" class="inline-flex items-center justify-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-900 shadow-sm transition hover:bg-neutral-100">Media toevoegen</a>
                </x-deyvo::empty-state>
            @else
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($media as $item)
                        @php($url = \Deyvo\Core\Support\SiteMedia::url($item->id))
                        <article class="overflow-hidden border border-neutral-200 bg-white shadow-sm">
                            <div class="flex aspect-video items-center justify-center bg-neutral-100">
                                @if ($url && str_starts_with((string) $item->mime_type, 'image/'))
                                    <img src="{{ $url }}" alt="{{ $item->alt ?? $item->name }}" class="h-full w-full object-cover">
                                @else
                                    <span class="px-4 text-center text-sm font-medium text-neutral-500">{{ $item->mime_type ?? 'Media' }}</span>
                                @endif
                            </div>
                            <div class="space-y-2 p-4">
                                <h2 class="truncate text-sm font-semibold text-neutral-950">{{ $item->name }}</h2>
                                <p class="truncate text-xs text-neutral-500">{{ $item->folder?->name ?? 'Geen map' }}</p>
                                <p class="truncate font-mono text-xs text-neutral-500">{{ $item->path ?? $item->url }}</p>
                                <div class="flex items-center justify-between gap-3 pt-2 text-sm font-medium">
                                    <a href="{{ route('deyvo.dashboard.media.edit', $item) }}" class="text-sky-700 hover:text-sky-900">Bewerken</a>
                                    <form method="POST" action="{{ route('deyvo.dashboard.media.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-700 hover:text-red-900">Verwijderen</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-5">{{ $media->links() }}</div>
            @endif
        </section>
    </div>
@endcomponent
