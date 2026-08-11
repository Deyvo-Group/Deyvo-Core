@component('deyvo::dashboard.layout', ['title' => 'Revisies'])
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-neutral-950">Revisies</h1>
            <p class="mt-2 text-sm text-neutral-600">Versiegeschiedenis voor {{ $page->key }}.</p>
        </div>

        <a href="{{ route('deyvo.dashboard.pages.edit', $page) }}" class="text-sm font-medium text-neutral-600 hover:text-neutral-950">Terug naar pagina</a>
    </div>

    <div class="mt-8 overflow-hidden border border-neutral-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 text-left">
                <thead class="bg-neutral-50">
                    <tr>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-neutral-500">Versie</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-neutral-500">Titel</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-neutral-500">Slug</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-neutral-500">Gewijzigd</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-neutral-500">Actie</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200">
                    @foreach ($revisions as $revision)
                        <tr>
                            <td class="px-5 py-4 text-sm font-medium text-neutral-950">v{{ $revision->version }}</td>
                            <td class="px-5 py-4 text-sm text-neutral-600">{{ $revision->title }}</td>
                            <td class="px-5 py-4 text-sm text-neutral-600">/{{ $revision->slug }}</td>
                            <td class="px-5 py-4 text-sm text-neutral-600">{{ $revision->updated_at->format('d-m-Y H:i') }}</td>
                            <td class="px-5 py-4 text-right">
                                <form method="POST" action="{{ route('deyvo.dashboard.pages.revisions.restore', [$page, $revision]) }}">
                                    @csrf
                                    <button type="submit" class="text-sm font-medium text-sky-700 hover:text-sky-900">Herstellen als concept</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $revisions->links() }}
    </div>
@endcomponent
