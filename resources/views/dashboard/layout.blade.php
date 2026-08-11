@props([
    'title' => 'Dashboard',
])

@php($appName = config('deyvo-core.name', config('app.name', 'Deyvo')))
@php($navigation = app(\Deyvo\Core\Dashboard\DashboardManager::class)->navigation())
@php($vite = config('deyvo-core.dashboard.vite', []))
@php($actor = app(\Deyvo\Core\Support\Actor::class)->current())
@php($gradient = config('deyvo-core.ui.dashboard.gradient'))

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-deyvo-core-styles="{{ config('deyvo-core.ui.styles.enabled', true) ? 'enabled' : 'disabled' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | {{ $appName }}</title>
    @if (is_array($vite) && $vite !== [])
        @vite($vite)
    @endif
</head>
<body class="min-h-screen bg-neutral-100 text-neutral-950 antialiased" data-deyvo-dashboard @if (is_string($gradient) && trim($gradient) !== '') style="--deyvo-dashboard-gradient: {{ $gradient }};" @endif>
    <div class="min-h-screen md:grid md:grid-cols-[16rem_minmax(0,1fr)]">
        <aside class="hidden bg-neutral-950 px-4 py-5 text-neutral-200 md:block" data-deyvo-dashboard-sidebar>
            <a href="{{ route('deyvo.dashboard.index') }}" class="flex items-center gap-3 px-3 text-lg font-semibold text-white" data-deyvo-dashboard-brand>
                <span class="inline-flex size-8 items-center justify-center text-sm font-bold" data-deyvo-dashboard-brand-mark>D</span>
                <span class="truncate">{{ $appName }}</span>
            </a>
            <p class="mt-1 px-3 text-xs font-medium text-neutral-400">Dashboard</p>

            <nav class="mt-8 space-y-1" aria-label="Dashboard navigatie">
                @foreach ($navigation as $item)
                    @php($isActive = request()->routeIs($item['active']) && (! isset($item['page']) || request()->route('page') === $item['page']))
                    <a href="{{ route($item['route'], $item['parameters'] ?? []) }}" data-deyvo-dashboard-nav @class([
                        'block rounded-md px-3 py-2 text-sm font-medium transition',
                        'bg-white/10 text-white' => $isActive,
                        'text-neutral-300 hover:bg-white/5 hover:text-white' => ! $isActive,
                    ])>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </aside>

        <div class="min-w-0">
            <header class="border-b border-neutral-200 bg-white" data-deyvo-dashboard-header>
                <div class="mx-auto flex min-h-16 max-w-7xl items-center gap-4 px-5 sm:px-8">
                    <a href="{{ route('deyvo.dashboard.index') }}" class="text-base font-semibold text-neutral-950 md:hidden">{{ $appName }}</a>

                    <nav class="flex min-w-0 flex-1 items-center gap-4 overflow-x-auto md:hidden" aria-label="Dashboard navigatie">
                        @foreach ($navigation as $item)
                            @php($isActive = request()->routeIs($item['active']) && (! isset($item['page']) || request()->route('page') === $item['page']))
                            <a href="{{ route($item['route'], $item['parameters'] ?? []) }}" @class([
                                'whitespace-nowrap text-sm font-medium',
                                'text-neutral-950' => $isActive,
                                'text-neutral-500 hover:text-neutral-950' => ! $isActive,
                            ])>{{ $item['label'] }}</a>
                        @endforeach
                    </nav>

                    <div class="ml-auto hidden min-w-0 text-right sm:block" data-deyvo-dashboard-user>
                        <p class="text-xs font-medium text-neutral-500">Aangemeld als</p>
                        <p class="truncate text-sm font-semibold text-neutral-950">{{ $actor['name'] ?? $actor['email'] ?? 'Onbekende gebruiker' }}</p>
                    </div>

                    <p class="hidden text-sm text-neutral-500 lg:block">{{ config('deyvo-core.version') }}</p>
                </div>
            </header>

            <main class="mx-auto w-full max-w-7xl px-5 py-8 sm:px-8" data-deyvo-dashboard-main>
                <x-deyvo::flash />
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
