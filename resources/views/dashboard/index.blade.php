@component('deyvo::dashboard.layout', ['title' => 'Overzicht'])
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-sky-700">{{ config('deyvo-core.name') }}</p>
            <h1 class="mt-1 text-2xl font-semibold text-neutral-950">Overzicht</h1>
            <p class="mt-2 text-sm text-neutral-600">Beheer de gedeelde content en instellingen van deze website.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('deyvo.dashboard.contents.create') }}" class="inline-flex items-center justify-center rounded-md bg-neutral-950 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-neutral-800">Content toevoegen</a>
            <a href="{{ route('deyvo.dashboard.settings.create') }}" class="inline-flex items-center justify-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-900 shadow-sm transition hover:bg-neutral-100">Instelling toevoegen</a>
        </div>
    </div>

    <dl @class([
        'mt-8 grid gap-4 sm:grid-cols-2',
        'xl:grid-cols-3' => ! config('deyvo-core.dashboard.pages.enabled', false),
        'xl:grid-cols-5' => config('deyvo-core.dashboard.pages.enabled', false),
    ])>
        <div class="rounded-md border border-neutral-200 bg-white px-5 py-4 shadow-sm">
            <dt class="text-sm font-medium text-neutral-600">Content items</dt>
            <dd class="mt-2 text-3xl font-semibold text-neutral-950">{{ $contentCount }}</dd>
        </div>
        <div class="rounded-md border border-neutral-200 bg-white px-5 py-4 shadow-sm">
            <dt class="text-sm font-medium text-neutral-600">Gepubliceerd</dt>
            <dd class="mt-2 text-3xl font-semibold text-neutral-950">{{ $publishedContentCount }}</dd>
        </div>
        <div class="rounded-md border border-neutral-200 bg-white px-5 py-4 shadow-sm">
            <dt class="text-sm font-medium text-neutral-600">Instellingen</dt>
            <dd class="mt-2 text-3xl font-semibold text-neutral-950">{{ $settingCount }}</dd>
        </div>
        @if (config('deyvo-core.dashboard.pages.enabled', false))
            <div class="rounded-md border border-neutral-200 bg-white px-5 py-4 shadow-sm">
                <dt class="text-sm font-medium text-neutral-600">Pagina’s</dt>
                <dd class="mt-2 text-3xl font-semibold text-neutral-950">{{ $pageCount }}</dd>
            </div>
            <div class="rounded-md border border-neutral-200 bg-white px-5 py-4 shadow-sm">
                <dt class="text-sm font-medium text-neutral-600">Pagina’s live</dt>
                <dd class="mt-2 text-3xl font-semibold text-neutral-950">{{ $publishedPageCount }}</dd>
            </div>
        @endif
    </dl>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <section class="border-t border-neutral-300 pt-5">
            <h2 class="text-base font-semibold text-neutral-950">Content</h2>
            <p class="mt-2 text-sm leading-6 text-neutral-600">Maak herbruikbare tekstblokken die in de website met hun sleutel kunnen worden opgehaald.</p>
            <a href="{{ route('deyvo.dashboard.contents.index') }}" class="mt-4 inline-flex text-sm font-medium text-sky-700 hover:text-sky-900">Content beheren</a>
        </section>

        <section class="border-t border-neutral-300 pt-5">
            <h2 class="text-base font-semibold text-neutral-950">Instellingen</h2>
            <p class="mt-2 text-sm leading-6 text-neutral-600">Bewaar sitebrede waarden zoals contactgegevens, labels en eenvoudige configuratie.</p>
            <a href="{{ route('deyvo.dashboard.settings.index') }}" class="mt-4 inline-flex text-sm font-medium text-sky-700 hover:text-sky-900">Instellingen beheren</a>
        </section>

        @if (config('deyvo-core.dashboard.pages.enabled', false))
            <section class="border-t border-neutral-300 pt-5">
                <h2 class="text-base font-semibold text-neutral-950">Pagina’s</h2>
                <p class="mt-2 text-sm leading-6 text-neutral-600">Werk met templates, secties, concepten, SEO en een versiegeschiedenis voordat een pagina live gaat.</p>
                <a href="{{ route('deyvo.dashboard.pages.index') }}" class="mt-4 inline-flex text-sm font-medium text-sky-700 hover:text-sky-900">Pagina’s beheren</a>
            </section>
        @endif
    </div>
@endcomponent
