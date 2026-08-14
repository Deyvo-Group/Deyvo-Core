@component('deyvo::dashboard.layout', ['title' => 'Overzicht'])
    @php($actorLabel = $actor['name'] ?? $actor['email'] ?? 'Onbekende gebruiker')
    @php($hasLayouts = app(\Deyvo\Core\Dashboard\DashboardManager::class)->layouts() !== [])
    @php($managedTotal = $contentCount + $settingCount + $pageCount + $mediaCount + $menuCount)
    @php($featureCards = [
        [
            'label' => 'Content',
            'description' => 'Herbruikbare teksten en blokken voor plekken door de hele website.',
            'href' => route('deyvo.dashboard.contents.index'),
            'meta' => $contentCount.' items',
            'tone' => 'content',
        ],
        [
            'label' => 'Instellingen',
            'description' => 'Sitebrede waarden zoals contactgegevens, labels en configuratie.',
            'href' => route('deyvo.dashboard.settings.index'),
            'meta' => $settingCount.' waarden',
            'tone' => 'settings',
        ],
    ])
    @if ($hasLayouts)
        @php($featureCards[] = [
            'label' => 'Header en footer',
            'description' => 'Globale onderdelen die op meerdere publieke pagina’s terugkomen.',
            'href' => route('deyvo.dashboard.layouts.index'),
            'meta' => 'Layout',
            'tone' => 'layout',
        ])
    @endif
    @if (config('deyvo-core.dashboard.pages.enabled', false))
        @php($featureCards[] = [
            'label' => 'Pagina’s',
            'description' => 'Templates, concepten, publicaties, SEO en revisies per pagina.',
            'href' => route('deyvo.dashboard.pages.index'),
            'meta' => $pageCount.' pagina’s',
            'tone' => 'pages',
        ])
    @endif
    @if (config('deyvo-core.dashboard.media.enabled', true))
        @php($featureCards[] = [
            'label' => 'Media',
            'description' => 'Afbeeldingen, downloads en mappen voor content en pagina’s.',
            'href' => route('deyvo.dashboard.media.index'),
            'meta' => $mediaCount.' bestanden',
            'tone' => 'media',
        ])
    @endif
    @if (config('deyvo-core.dashboard.menus.enabled', true))
        @php($featureCards[] = [
            'label' => 'Menu’s',
            'description' => 'Header-, footer- en campagnemenu’s als beheerde structuren.',
            'href' => route('deyvo.dashboard.menus.index'),
            'meta' => $menuCount.' menu’s',
            'tone' => 'menus',
        ])
    @endif
    @if (config('deyvo-core.dashboard.seo.enabled', true))
        @php($featureCards[] = [
            'label' => 'SEO',
            'description' => 'Globale titels, descriptions, indexering en social previews.',
            'href' => route('deyvo.dashboard.seo.index'),
            'meta' => 'Defaults',
            'tone' => 'seo',
        ])
    @endif
    @if (config('deyvo-core.dashboard.users.enabled', true))
        @php($featureCards[] = [
            'label' => 'Users',
            'description' => 'Dashboardgebruikers via het gekoppelde Laravel user-model.',
            'href' => route('deyvo.dashboard.users.index'),
            'meta' => 'Toegang',
            'tone' => 'users',
        ])
    @endif

    <section class="-mx-5 -mt-8 grid gap-6 border-b border-neutral-200 px-5 py-8 sm:-mx-8 sm:px-8 lg:grid-cols-[minmax(0,1.35fr)_minmax(20rem,0.65fr)]" data-deyvo-dashboard-hero>
        <div class="min-w-0">
            <p class="text-sm font-semibold text-sky-700">{{ config('deyvo-core.name') }}</p>
            <h1 class="mt-2 text-3xl font-semibold text-neutral-950 sm:text-4xl">Overzicht</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-neutral-600">Beheer content, pagina’s en website-instellingen vanuit een compacte werkruimte.</p>
            <p class="mt-4 text-sm text-neutral-600">Aangemeld als <span class="font-semibold text-neutral-950">{{ $actorLabel }}</span>.</p>
        </div>

        <div class="rounded-md border border-neutral-200 bg-white p-4 shadow-sm" data-deyvo-dashboard-action-panel>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-neutral-950">{{ $managedTotal }}</p>
                    <p class="mt-1 text-xs font-medium text-neutral-500">beheerobjecten</p>
                </div>
                <span class="rounded-md bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-800">Actief</span>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                <a href="{{ route('deyvo.dashboard.contents.create') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-neutral-950 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-neutral-800">
                    <span aria-hidden="true" class="text-base leading-none">+</span>
                    Content toevoegen
                </a>
                <a href="{{ route('deyvo.dashboard.settings.create') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-neutral-300 bg-white px-4 py-2.5 text-sm font-semibold text-neutral-900 shadow-sm transition hover:bg-neutral-100">
                    <span aria-hidden="true" class="text-base leading-none">+</span>
                    Instelling toevoegen
                </a>
            </div>
        </div>
    </section>

    <dl data-deyvo-dashboard-stats class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @if ($widgets['content'] ?? true)
            <div data-deyvo-dashboard-stat data-deyvo-dashboard-stat-tone="content" class="rounded-md border border-neutral-200 bg-white px-5 py-5 shadow-sm">
                <dt class="flex items-center justify-between gap-3 text-sm font-medium text-neutral-600">
                    <span>Content items</span>
                    <span class="text-xs font-semibold text-neutral-400">Totaal</span>
                </dt>
                <dd class="mt-4 text-3xl font-semibold text-neutral-950">{{ $contentCount }}</dd>
            </div>
            <div data-deyvo-dashboard-stat data-deyvo-dashboard-stat-tone="published" class="rounded-md border border-neutral-200 bg-white px-5 py-5 shadow-sm">
                <dt class="flex items-center justify-between gap-3 text-sm font-medium text-neutral-600">
                    <span>Gepubliceerd</span>
                    <span class="text-xs font-semibold text-emerald-600">Live</span>
                </dt>
                <dd class="mt-4 text-3xl font-semibold text-neutral-950">{{ $publishedContentCount }}</dd>
            </div>
        @endif
        @if ($widgets['settings'] ?? true)
            <div data-deyvo-dashboard-stat data-deyvo-dashboard-stat-tone="settings" class="rounded-md border border-neutral-200 bg-white px-5 py-5 shadow-sm">
                <dt class="flex items-center justify-between gap-3 text-sm font-medium text-neutral-600">
                    <span>Instellingen</span>
                    <span class="text-xs font-semibold text-neutral-400">Waarden</span>
                </dt>
                <dd class="mt-4 text-3xl font-semibold text-neutral-950">{{ $settingCount }}</dd>
            </div>
        @endif
        @if (($widgets['pages'] ?? true) && config('deyvo-core.dashboard.pages.enabled', false))
            <div data-deyvo-dashboard-stat data-deyvo-dashboard-stat-tone="pages" class="rounded-md border border-neutral-200 bg-white px-5 py-5 shadow-sm">
                <dt class="flex items-center justify-between gap-3 text-sm font-medium text-neutral-600">
                    <span>Pagina’s</span>
                    <span class="text-xs font-semibold text-neutral-400">Totaal</span>
                </dt>
                <dd class="mt-4 text-3xl font-semibold text-neutral-950">{{ $pageCount }}</dd>
            </div>
            <div data-deyvo-dashboard-stat data-deyvo-dashboard-stat-tone="published" class="rounded-md border border-neutral-200 bg-white px-5 py-5 shadow-sm">
                <dt class="flex items-center justify-between gap-3 text-sm font-medium text-neutral-600">
                    <span>Pagina’s live</span>
                    <span class="text-xs font-semibold text-emerald-600">Live</span>
                </dt>
                <dd class="mt-4 text-3xl font-semibold text-neutral-950">{{ $publishedPageCount }}</dd>
            </div>
        @endif
        @if (($widgets['media'] ?? true) && config('deyvo-core.dashboard.media.enabled', true))
            <div data-deyvo-dashboard-stat data-deyvo-dashboard-stat-tone="media" class="rounded-md border border-neutral-200 bg-white px-5 py-5 shadow-sm">
                <dt class="flex items-center justify-between gap-3 text-sm font-medium text-neutral-600">
                    <span>Media</span>
                    <span class="text-xs font-semibold text-neutral-400">Bestanden</span>
                </dt>
                <dd class="mt-4 text-3xl font-semibold text-neutral-950">{{ $mediaCount }}</dd>
            </div>
        @endif
        @if (($widgets['menus'] ?? true) && config('deyvo-core.dashboard.menus.enabled', true))
            <div data-deyvo-dashboard-stat data-deyvo-dashboard-stat-tone="menus" class="rounded-md border border-neutral-200 bg-white px-5 py-5 shadow-sm">
                <dt class="flex items-center justify-between gap-3 text-sm font-medium text-neutral-600">
                    <span>Menu’s</span>
                    <span class="text-xs font-semibold text-neutral-400">Structuren</span>
                </dt>
                <dd class="mt-4 text-3xl font-semibold text-neutral-950">{{ $menuCount }}</dd>
            </div>
        @endif
    </dl>

    <section class="mt-8" data-deyvo-dashboard-workspace>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-base font-semibold text-neutral-950">Werkruimte</h2>
                <p class="mt-1 text-sm text-neutral-600">Snel naar de onderdelen die deze website beheersbaar maken.</p>
            </div>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($featureCards as $feature)
                <article data-deyvo-dashboard-feature-card data-deyvo-dashboard-feature="{{ $feature['tone'] }}" class="rounded-md border border-neutral-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <span data-deyvo-dashboard-feature-mark aria-hidden="true"></span>
                        <span class="text-xs font-semibold text-neutral-500">{{ $feature['meta'] }}</span>
                    </div>
                    <h3 class="mt-5 text-base font-semibold text-neutral-950">{{ $feature['label'] }}</h3>
                    <p class="mt-2 min-h-12 text-sm leading-6 text-neutral-600">{{ $feature['description'] }}</p>
                    <a href="{{ $feature['href'] }}" class="mt-5 inline-flex text-sm font-semibold text-sky-700 hover:text-sky-900">Openen</a>
                </article>
            @endforeach
        </div>
    </section>

    @if (($widgets['activity'] ?? true) && config('deyvo-core.audit.enabled', true))
        <section class="mt-10" data-deyvo-dashboard-activity-panel>
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-neutral-200 pb-4">
                <div>
                    <h2 class="text-base font-semibold text-neutral-950">Recente activiteit</h2>
                    <p class="mt-1 text-sm text-neutral-600">Wijzigingen, previews en foutmeldingen binnen Core.</p>
                </div>

                <a href="{{ route('deyvo.dashboard.activity.index') }}" class="text-sm font-semibold text-sky-700 hover:text-sky-900">Alle activiteit</a>
            </div>

            @if ($recentActivities->isEmpty())
                <div class="mt-5 rounded-md border border-dashed border-neutral-300 bg-white px-5 py-8 text-sm text-neutral-600">
                    Er is nog geen activiteit geregistreerd.
                </div>
            @else
                <div class="mt-5 overflow-hidden rounded-md border border-neutral-200 bg-white shadow-sm" data-deyvo-dashboard-table>
                    <div class="overflow-x-auto">
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
                                        <td class="px-5 py-4 text-right"><a href="{{ route('deyvo.dashboard.activity.show', $activity) }}" class="text-sm font-semibold text-sky-700 hover:text-sky-900">Details</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </section>
    @endif
@endcomponent
