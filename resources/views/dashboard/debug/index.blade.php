@php($json = static fn (mixed $value): string => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))

@component('deyvo::dashboard.layout', ['title' => 'Debug'])
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-sky-700">Core</p>
            <h1 class="mt-1 text-2xl font-semibold text-neutral-950">Debug</h1>
            <p class="mt-2 text-sm text-neutral-600">Live diagnose voor dashboardroutes, pagina’s, revisies, schema en cache.</p>
        </div>

        <x-deyvo::badge variant="warning">DEYVO_DEBUG_ENABLED</x-deyvo::badge>
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-2">
        <section class="border border-neutral-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-semibold text-neutral-950">Request</h2>
            <dl class="mt-4 divide-y divide-neutral-200 text-sm">
                @foreach ($requestInfo as $key => $value)
                    <div class="grid gap-3 py-2 sm:grid-cols-[12rem_minmax(0,1fr)]">
                        <dt class="font-mono text-xs text-neutral-500">{{ $key }}</dt>
                        <dd class="break-words text-neutral-900">{{ is_scalar($value) || $value === null ? var_export($value, true) : $json($value) }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <section class="border border-neutral-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-semibold text-neutral-950">Config En Cache</h2>
            <dl class="mt-4 divide-y divide-neutral-200 text-sm">
                @foreach ([...$config, ...$cache] as $key => $value)
                    <div class="grid gap-3 py-2 sm:grid-cols-[18rem_minmax(0,1fr)]">
                        <dt class="font-mono text-xs text-neutral-500">{{ $key }}</dt>
                        <dd class="break-words text-neutral-900">{{ is_scalar($value) || $value === null ? var_export($value, true) : $json($value) }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <section class="border border-neutral-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-semibold text-neutral-950">Database</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 text-left text-sm">
                    <thead class="bg-neutral-50 text-xs font-medium uppercase text-neutral-500">
                        <tr>
                            <th class="px-4 py-3">Tabel</th>
                            <th class="px-4 py-3">Bestaat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200">
                        @foreach ($database as $table)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs text-neutral-600">{{ $table['table'] }}</td>
                                <td class="px-4 py-3">
                                    <x-deyvo::badge :variant="$table['exists'] ? 'success' : 'danger'">{{ $table['exists'] ? 'Ja' : 'Nee' }}</x-deyvo::badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="border border-neutral-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-semibold text-neutral-950">Aantallen</h2>
            <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($counts as $key => $value)
                    <div class="border border-neutral-200 p-4">
                        <dt class="text-sm font-medium text-neutral-600">{{ $key }}</dt>
                        <dd class="mt-2 text-2xl font-semibold text-neutral-950">{{ $value ?? 'n/a' }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <section class="border border-neutral-200 bg-white p-5 shadow-sm xl:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-neutral-950">Pagina Diagnose</h2>
                    <p class="mt-1 text-sm text-neutral-600">Let hier vooral op revision_count en de edit_url van page id 1.</p>
                </div>

                @if ($pageDiagnostics['orphans'] !== [])
                    <x-deyvo::badge variant="warning">{{ count($pageDiagnostics['orphans']) }} verdachte pagina’s</x-deyvo::badge>
                @else
                    <x-deyvo::badge variant="success">Geen verdachte pagina’s</x-deyvo::badge>
                @endif
            </div>

            <pre class="mt-4 max-h-[32rem] overflow-auto bg-neutral-950 p-4 text-xs leading-6 text-neutral-100">{{ $json($pageDiagnostics) }}</pre>
        </section>

        <section class="border border-neutral-200 bg-white p-5 shadow-sm xl:col-span-2">
            <h2 class="text-base font-semibold text-neutral-950">Legacy Tabellen</h2>
            <p class="mt-1 text-sm text-neutral-600">Als page id 1 hier staat maar niet in deyvo_pages, moet de legacy-import nog draaien.</p>
            <pre class="mt-4 max-h-[32rem] overflow-auto bg-neutral-950 p-4 text-xs leading-6 text-neutral-100">{{ $json($legacy) }}</pre>
        </section>

        <section class="border border-neutral-200 bg-white p-5 shadow-sm xl:col-span-2">
            <h2 class="text-base font-semibold text-neutral-950">Schema</h2>
            <pre class="mt-4 overflow-auto bg-neutral-950 p-4 text-xs leading-6 text-neutral-100">{{ $json($schema) }}</pre>
        </section>

        <section class="border border-neutral-200 bg-white p-5 shadow-sm xl:col-span-2">
            <h2 class="text-base font-semibold text-neutral-950">Dashboardroutes</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 text-left text-sm">
                    <thead class="bg-neutral-50 text-xs font-medium uppercase text-neutral-500">
                        <tr>
                            <th class="px-4 py-3">Method</th>
                            <th class="px-4 py-3">URI</th>
                            <th class="px-4 py-3">Naam</th>
                            <th class="px-4 py-3">Middleware</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200">
                        @foreach ($routes as $route)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-neutral-600">{{ $route['method'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-neutral-600">{{ $route['uri'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-neutral-600">{{ $route['name'] }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-neutral-500">{{ implode(', ', $route['middleware']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endcomponent
