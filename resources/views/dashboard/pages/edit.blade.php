@component('deyvo::dashboard.layout', ['title' => $revision->title])
    <div class="max-w-3xl">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-neutral-950">{{ $revision->title }}</h1>
                <p class="mt-2 text-sm text-neutral-600">Versie {{ $revision->version }} als {{ $page->draft_revision_id ? 'concept' : 'gepubliceerde pagina' }}.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('deyvo.dashboard.pages.revisions', $page) }}" class="text-sm font-medium text-neutral-600 hover:text-neutral-950">Revisies</a>
                <a href="{{ route('deyvo.dashboard.pages.preview', $page) }}" target="_blank" rel="noreferrer" class="text-sm font-medium text-sky-700 hover:text-sky-900">Preview</a>

                @if ($page->draft_revision_id)
                    <form method="POST" action="{{ route('deyvo.dashboard.pages.publish', $page) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-emerald-800">Publiceren</button>
                    </form>
                @endif
            </div>
        </div>

        @include('deyvo::dashboard.pages.form', [
            'action' => route('deyvo.dashboard.pages.update', $page),
            'method' => 'PUT',
            'page' => $page,
            'revision' => $revision,
            'template' => $template,
        ])
    </div>
@endcomponent
