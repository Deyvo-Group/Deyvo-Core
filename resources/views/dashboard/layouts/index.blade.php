@component('deyvo::dashboard.layout', ['title' => 'Layout'])
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-sky-700">Website-indeling</p>
            <h1 class="mt-1 text-2xl font-semibold text-neutral-950">Header en footer</h1>
            <p class="mt-2 text-sm text-neutral-600">Beheer de globale inhoud die op meerdere pagina's van de website terugkomt.</p>
        </div>
    </div>

    @if ($layouts === [])
        <x-deyvo::empty-state class="mt-8" title="Nog geen layoutonderdelen" description="Voeg header- en footerdefinities toe aan het Deyvo dashboard-schema." />
    @else
        <div class="mt-8 grid gap-4 sm:grid-cols-2" data-deyvo-dashboard-layouts>
            @foreach ($layouts as $layout)
                <article class="border border-neutral-200 bg-white p-5 shadow-sm" data-deyvo-dashboard-layout>
                    <p class="text-sm font-medium text-sky-700">Website-indeling</p>
                    <h2 class="mt-2 text-lg font-semibold text-neutral-950">{{ $layout['label'] }}</h2>
                    <p class="mt-2 text-sm leading-6 text-neutral-600">{{ $layout['description'] ?? 'Beheer de zichtbare velden van dit layoutonderdeel.' }}</p>
                    <p class="mt-5 text-sm text-neutral-500">{{ count($layout['fields']) }} {{ count($layout['fields']) === 1 ? 'veld' : 'velden' }}</p>
                    <a href="{{ route('deyvo.dashboard.layouts.show', ['layout' => $layout['key']]) }}" class="mt-5 inline-flex text-sm font-medium text-sky-700 hover:text-sky-900">Bewerken</a>
                </article>
            @endforeach
        </div>
    @endif
@endcomponent
