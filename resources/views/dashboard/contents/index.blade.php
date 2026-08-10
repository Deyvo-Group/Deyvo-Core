@component('deyvo::dashboard.layout', ['title' => 'Content'])
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-sky-700">Dashboard</p>
            <h1 class="mt-1 text-2xl font-semibold text-neutral-950">Content</h1>
            <p class="mt-2 text-sm text-neutral-600">Beheer herbruikbare contentblokken per sleutel.</p>
        </div>

        <a href="{{ route('deyvo.dashboard.contents.create') }}" class="inline-flex items-center justify-center rounded-md bg-neutral-950 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-neutral-800">Content toevoegen</a>
    </div>

    @if ($contents->isEmpty())
        <x-deyvo::empty-state class="mt-8" title="Nog geen content" description="Maak een eerste contentblok voor deze website.">
            <a href="{{ route('deyvo.dashboard.contents.create') }}" class="inline-flex items-center justify-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-900 shadow-sm transition hover:bg-neutral-100">Content toevoegen</a>
        </x-deyvo::empty-state>
    @else
        <div class="mt-8 overflow-x-auto border border-neutral-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-neutral-200 text-left text-sm">
                <thead class="bg-neutral-50 text-xs font-medium uppercase text-neutral-500">
                    <tr>
                        <th scope="col" class="px-5 py-3">Sleutel</th>
                        <th scope="col" class="px-5 py-3">Titel</th>
                        <th scope="col" class="px-5 py-3">Status</th>
                        <th scope="col" class="px-5 py-3">Bijgewerkt</th>
                        <th scope="col" class="px-5 py-3"><span class="sr-only">Acties</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200">
                    @foreach ($contents as $content)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-4 font-mono text-xs text-neutral-600">{{ $content->key }}</td>
                            <td class="px-5 py-4 font-medium text-neutral-950">{{ $content->title }}</td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <x-deyvo::badge :variant="$content->is_published ? 'success' : 'neutral'">{{ $content->is_published ? 'Gepubliceerd' : 'Concept' }}</x-deyvo::badge>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-neutral-600">{{ $content->updated_at->diffForHumans() }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-3">
                                    <a href="{{ route('deyvo.dashboard.contents.edit', $content) }}" class="text-sm font-medium text-sky-700 hover:text-sky-900">Bewerken</a>
                                    <form method="POST" action="{{ route('deyvo.dashboard.contents.destroy', $content) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-medium text-red-700 hover:text-red-900">Verwijderen</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $contents->links() }}</div>
    @endif
@endcomponent
