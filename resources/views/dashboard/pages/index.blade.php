@component('deyvo::dashboard.layout', ['title' => 'Pagina’s'])
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-neutral-950">Pagina’s</h1>
            <p class="mt-2 text-sm text-neutral-600">Beheer concepten, publicaties en revisies.</p>
        </div>

        @if ($hasTemplates)
            <a href="{{ route('deyvo.dashboard.pages.create') }}" class="inline-flex items-center justify-center rounded-md bg-neutral-950 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-neutral-800">Pagina toevoegen</a>
        @endif
    </div>

    @if (! $hasTemplates)
        <div class="mt-8 border border-dashed border-neutral-300 bg-white px-5 py-8 text-sm text-neutral-600">
            Voeg eerst pagina-templates toe aan het Deyvo dashboard-schema.
        </div>
    @elseif ($pages->isEmpty())
        <div class="mt-8 border border-dashed border-neutral-300 bg-white px-5 py-8 text-sm text-neutral-600">
            Er zijn nog geen pagina’s aangemaakt.
        </div>
    @else
        <div class="mt-8 overflow-hidden border border-neutral-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 text-left">
                    <thead class="bg-neutral-50">
                        <tr>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-neutral-500">Pagina</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-neutral-500">Slug</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-neutral-500">Status</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-neutral-500">Laatste wijziging</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-neutral-500">Acties</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200">
                        @foreach ($pages as $page)
                            @php($revision = $page->draftRevision ?? $page->publishedRevision)
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-medium text-neutral-950">{{ $revision?->title ?? $page->key }}</p>
                                    <p class="mt-1 text-sm text-neutral-500">{{ $revision?->template }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-neutral-600">/{{ $revision?->slug }}</td>
                                <td class="px-5 py-4">
                                    @if ($page->draft_revision_id)
                                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Concept</span>
                                    @elseif ($page->published_revision_id)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Gepubliceerd</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-neutral-100 px-2.5 py-1 text-xs font-semibold text-neutral-700">Leeg</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm text-neutral-600">{{ $revision?->updated_at?->format('d-m-Y H:i') }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-4 text-sm font-medium">
                                        <a href="{{ route('deyvo.dashboard.pages.edit', $page) }}" class="text-sky-700 hover:text-sky-900">Bewerken</a>
                                        <a href="{{ route('deyvo.dashboard.pages.revisions', $page) }}" class="text-neutral-600 hover:text-neutral-950">Revisies</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $pages->links() }}
        </div>
    @endif
@endcomponent
