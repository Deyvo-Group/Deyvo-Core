@component('deyvo::dashboard.layout', ['title' => 'Overzicht'])
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-sky-700">{{ config('deyvo-core.name') }}</p>
            <h1 class="mt-1 text-2xl font-semibold text-neutral-950">Overzicht</h1>
            <p class="mt-2 text-sm text-neutral-600">Beheer de gedeelde content en instellingen van deze website.</p>
            <p class="mt-3 text-sm text-neutral-600">Aangemeld als <span class="font-medium text-neutral-950">{{ $actor['name'] ?? $actor['email'] ?? 'Onbekende gebruiker' }}</span>.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('deyvo.dashboard.contents.create') }}" class="inline-flex items-center justify-center rounded-md bg-neutral-950 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-neutral-800">Content toevoegen</a>
            <a href="{{ route('deyvo.dashboard.settings.create') }}" class="inline-flex items-center justify-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-900 shadow-sm transition hover:bg-neutral-100">Instelling toevoegen</a>
        </div>
    </div>

    <dl data-deyvo-dashboard-stats class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @if ($widgets['content'] ?? true)
            <div data-deyvo-dashboard-stat class="rounded-md border border-neutral-200 bg-white px-5 py-4 shadow-sm">
                <dt class="text-sm font-medium text-neutral-600">Content items</dt>
                <dd class="mt-2 text-3xl font-semibold text-neutral-950">{{ $contentCount }}</dd>
            </div>
            <div data-deyvo-dashboard-stat class="rounded-md border border-neutral-200 bg-white px-5 py-4 shadow-sm">
                <dt class="text-sm font-medium text-neutral-600">Gepubliceerd</dt>
                <dd class="mt-2 text-3xl font-semibold text-neutral-950">{{ $publishedContentCount }}</dd>
            </div>
        @endif
        @if ($widgets['settings'] ?? true)
            <div data-deyvo-dashboard-stat class="rounded-md border border-neutral-200 bg-white px-5 py-4 shadow-sm">
                <dt class="text-sm font-medium text-neutral-600">Instellingen</dt>
                <dd class="mt-2 text-3xl font-semibold text-neutral-950">{{ $settingCount }}</dd>
            </div>
        @endif
        @if (($widgets['pages'] ?? true) && config('deyvo-core.dashboard.pages.enabled', false))
            <div data-deyvo-dashboard-stat class="rounded-md border border-neutral-200 bg-white px-5 py-4 shadow-sm">
                <dt class="text-sm font-medium text-neutral-600">Pagina’s</dt>
                <dd class="mt-2 text-3xl font-semibold text-neutral-950">{{ $pageCount }}</dd>
            </div>
            <div data-deyvo-dashboard-stat class="rounded-md border border-neutral-200 bg-white px-5 py-4 shadow-sm">
                <dt class="text-sm font-medium text-neutral-600">Pagina’s live</dt>
                <dd class="mt-2 text-3xl font-semibold text-neutral-950">{{ $publishedPageCount }}</dd>
            </div>
        @endif
        @if (($widgets['media'] ?? true) && config('deyvo-core.dashboard.media.enabled', true))
            <div data-deyvo-dashboard-stat class="rounded-md border border-neutral-200 bg-white px-5 py-4 shadow-sm">
                <dt class="text-sm font-medium text-neutral-600">Media</dt>
                <dd class="mt-2 text-3xl font-semibold text-neutral-950">{{ $mediaCount }}</dd>
            </div>
        @endif
        @if (($widgets['menus'] ?? true) && config('deyvo-core.dashboard.menus.enabled', true))
            <div data-deyvo-dashboard-stat class="rounded-md border border-neutral-200 bg-white px-5 py-4 shadow-sm">
                <dt class="text-sm font-medium text-neutral-600">Menu’s</dt>
                <dd class="mt-2 text-3xl font-semibold text-neutral-950">{{ $menuCount }}</dd>
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

        @if (app(\Deyvo\Core\Dashboard\DashboardManager::class)->layouts() !== [])
            <section class="border-t border-neutral-300 pt-5">
                <h2 class="text-base font-semibold text-neutral-950">Header en footer</h2>
                <p class="mt-2 text-sm leading-6 text-neutral-600">Werk de globale inhoud bij die bezoekers in de header en footer zien.</p>
                <a href="{{ route('deyvo.dashboard.layouts.index') }}" class="mt-4 inline-flex text-sm font-medium text-sky-700 hover:text-sky-900">Layout beheren</a>
            </section>
        @endif

        @if (config('deyvo-core.dashboard.pages.enabled', false))
            <section class="border-t border-neutral-300 pt-5">
                <h2 class="text-base font-semibold text-neutral-950">Pagina’s</h2>
                <p class="mt-2 text-sm leading-6 text-neutral-600">Werk met templates, secties, concepten, SEO en een versiegeschiedenis voordat een pagina live gaat.</p>
                <a href="{{ route('deyvo.dashboard.pages.index') }}" class="mt-4 inline-flex text-sm font-medium text-sky-700 hover:text-sky-900">Pagina’s beheren</a>
            </section>
        @endif

        @if (config('deyvo-core.dashboard.media.enabled', true))
            <section class="border-t border-neutral-300 pt-5">
                <h2 class="text-base font-semibold text-neutral-950">Media</h2>
                <p class="mt-2 text-sm leading-6 text-neutral-600">Beheer afbeeldingen, downloads en mappen voor hergebruik in pagina’s en templates.</p>
                <a href="{{ route('deyvo.dashboard.media.index') }}" class="mt-4 inline-flex text-sm font-medium text-sky-700 hover:text-sky-900">Media beheren</a>
            </section>
        @endif

        @if (config('deyvo-core.dashboard.menus.enabled', true))
            <section class="border-t border-neutral-300 pt-5">
                <h2 class="text-base font-semibold text-neutral-950">Menu’s</h2>
                <p class="mt-2 text-sm leading-6 text-neutral-600">Onderhoud header-, footer- en campagnemenu’s als beheerde JSON-structuren.</p>
                <a href="{{ route('deyvo.dashboard.menus.index') }}" class="mt-4 inline-flex text-sm font-medium text-sky-700 hover:text-sky-900">Menu’s beheren</a>
            </section>
        @endif

        @if (config('deyvo-core.dashboard.seo.enabled', true))
            <section class="border-t border-neutral-300 pt-5">
                <h2 class="text-base font-semibold text-neutral-950">SEO</h2>
                <p class="mt-2 text-sm leading-6 text-neutral-600">Stel globale titels, descriptions, indexering en social previews in.</p>
                <a href="{{ route('deyvo.dashboard.seo.index') }}" class="mt-4 inline-flex text-sm font-medium text-sky-700 hover:text-sky-900">SEO beheren</a>
            </section>
        @endif

        @if (config('deyvo-core.dashboard.users.enabled', true))
            <section class="border-t border-neutral-300 pt-5">
                <h2 class="text-base font-semibold text-neutral-950">Users</h2>
                <p class="mt-2 text-sm leading-6 text-neutral-600">Gebruik het Laravel user-model zonder host-specifieke dashboardcontrollers.</p>
                <a href="{{ route('deyvo.dashboard.users.index') }}" class="mt-4 inline-flex text-sm font-medium text-sky-700 hover:text-sky-900">Users beheren</a>
            </section>
        @endif
    </div>

    @if (($widgets['activity'] ?? true) && config('deyvo-core.audit.enabled', true))
        <section class="mt-10 border-t border-neutral-300 pt-5">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-neutral-950">Recente activiteit</h2>
                    <p class="mt-1 text-sm text-neutral-600">Wijzigingen, previews en foutmeldingen binnen Core.</p>
                </div>

                <a href="{{ route('deyvo.dashboard.activity.index') }}" class="text-sm font-medium text-sky-700 hover:text-sky-900">Alle activiteit</a>
            </div>

            @if ($recentActivities->isEmpty())
                <p class="mt-5 text-sm text-neutral-600">Er is nog geen activiteit geregistreerd.</p>
            @else
                <div class="mt-5 overflow-x-auto border border-neutral-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-neutral-200 text-left text-sm">
                        <thead class="bg-neutral-50 text-xs font-medium uppercase text-neutral-500">
                            <tr>
                                <th class="px-5 py-3">Activiteit</th>
                                <th class="px-5 py-3">Door</th>
                                <th class="px-5 py-3">Moment</th>
                                <th class="px-5 py-3"><span class="sr-only">Details</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200">
                            @foreach ($recentActivities as $activity)
                                <tr>
                                    <td class="px-5 py-4"><p class="font-medium text-neutral-950">{{ $activity->eventLabel() }}</p><p class="mt-1 text-xs text-neutral-500">{{ $activity->subject_label ?? 'Dashboard' }}</p></td>
                                    <td class="px-5 py-4 text-neutral-600">{{ $activity->actorLabel() }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-neutral-600">{{ $activity->created_at->diffForHumans() }}</td>
                                    <td class="px-5 py-4 text-right"><a href="{{ route('deyvo.dashboard.activity.show', $activity) }}" class="text-sm font-medium text-sky-700 hover:text-sky-900">Details</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endif
@endcomponent
