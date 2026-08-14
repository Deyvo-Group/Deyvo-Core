@props([
    'title' => 'Dashboard',
])

@php($appName = config('deyvo-core.name', config('app.name', 'Deyvo')))
@php($navigation = app(\Deyvo\Core\Dashboard\DashboardManager::class)->navigation())
@php($vite = config('deyvo-core.dashboard.vite', []))
@php($actor = app(\Deyvo\Core\Support\Actor::class)->current())
@php($gradient = config('deyvo-core.ui.dashboard.gradient'))
@php($logoutRoute = config('deyvo-core.dashboard.logout_route', 'logout'))
@php($logoutUrl = is_string($logoutRoute) && $logoutRoute !== '' && \Illuminate\Support\Facades\Route::has($logoutRoute) ? route($logoutRoute) : null)
@php($actorLabel = $actor['name'] ?? $actor['email'] ?? 'Onbekende gebruiker')
@php($brandInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(trim((string) $appName), 0, 1) ?: 'D'))
@php($actorInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(trim((string) $actorLabel), 0, 1) ?: 'U'))

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
    <div class="min-h-screen md:grid md:grid-cols-[17.5rem_minmax(0,1fr)]" data-deyvo-dashboard-shell>
        <aside class="hidden px-4 py-5 text-neutral-200 md:flex md:flex-col" data-deyvo-dashboard-sidebar>
            <a href="{{ route('deyvo.dashboard.index') }}" class="flex items-center gap-3 px-3 text-lg font-semibold text-white" data-deyvo-dashboard-brand>
                <span class="inline-flex size-9 items-center justify-center text-sm font-bold" data-deyvo-dashboard-brand-mark>{{ $brandInitial }}</span>
                <span class="min-w-0">
                    <span class="block truncate">{{ $appName }}</span>
                    <span class="mt-0.5 block text-xs font-medium text-neutral-400">Core dashboard</span>
                </span>
            </a>

            <nav class="mt-8 space-y-1" aria-label="Dashboard navigatie">
                @foreach ($navigation as $item)
                    @php($isActive = request()->routeIs($item['active']) && (! isset($item['page']) || request()->route('page') === $item['page']))
                    <a href="{{ route($item['route'], $item['parameters'] ?? []) }}" data-deyvo-dashboard-nav @class([
                        'block rounded-md px-3 py-2.5 text-sm font-medium transition',
                        'bg-white/10 text-white' => $isActive,
                        'text-neutral-300 hover:bg-white/5 hover:text-white' => ! $isActive,
                    ])>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="mt-auto rounded-md border border-white/10 px-3 py-3 text-xs text-neutral-400" data-deyvo-dashboard-sidebar-footer>
                <p class="font-semibold text-neutral-200">Deyvo Core</p>
                <p class="mt-1 truncate">{{ config('deyvo-core.version') }}</p>
            </div>
        </aside>

        <div class="min-w-0">
            <header class="sticky top-0 z-30 border-b border-neutral-200 bg-white" data-deyvo-dashboard-header>
                <div class="mx-auto flex min-h-16 max-w-[88rem] items-center gap-4 px-4 sm:px-6 lg:px-8">
                    <a href="{{ route('deyvo.dashboard.index') }}" class="flex items-center gap-2 text-base font-semibold text-neutral-950 md:hidden">
                        <span class="inline-flex size-8 items-center justify-center rounded-md bg-neutral-950 text-sm font-bold text-white" data-deyvo-dashboard-mobile-mark>{{ $brandInitial }}</span>
                        <span class="max-w-28 truncate sm:max-w-44">{{ $appName }}</span>
                    </a>

                    <nav class="flex min-w-0 flex-1 items-center gap-2 overflow-x-auto md:hidden" aria-label="Dashboard navigatie" data-deyvo-dashboard-mobile-nav>
                        @foreach ($navigation as $item)
                            @php($isActive = request()->routeIs($item['active']) && (! isset($item['page']) || request()->route('page') === $item['page']))
                            <a href="{{ route($item['route'], $item['parameters'] ?? []) }}" data-deyvo-dashboard-mobile-nav-item @class([
                                'whitespace-nowrap rounded-md px-3 py-1.5 text-sm font-medium transition',
                                'bg-neutral-950 text-white' => $isActive,
                                'text-neutral-600 hover:bg-neutral-100 hover:text-neutral-950' => ! $isActive,
                            ])>{{ $item['label'] }}</a>
                        @endforeach
                    </nav>

                    <div class="ml-auto flex min-w-0 shrink-0 items-center gap-3" data-deyvo-dashboard-account>
                        <span class="hidden size-9 shrink-0 items-center justify-center rounded-md bg-neutral-950 text-sm font-semibold text-white sm:inline-flex" data-deyvo-dashboard-user-mark>{{ $actorInitial }}</span>
                        <div class="min-w-0 text-right" data-deyvo-dashboard-user>
                            <p class="hidden text-xs font-medium text-neutral-500 sm:block">Aangemeld als</p>
                            <p class="max-w-36 truncate text-sm font-semibold text-neutral-950 sm:max-w-52">{{ $actorLabel }}</p>
                        </div>

                        @if ($logoutUrl !== null)
                            <form method="POST" action="{{ $logoutUrl }}">
                                @csrf
                                <button type="submit" title="Uitloggen" aria-label="Uitloggen" class="inline-flex h-9 items-center justify-center px-3 text-sm font-semibold" data-deyvo-dashboard-logout>Uitloggen</button>
                            </form>
                        @endif
                    </div>
                </div>
            </header>

            <main class="mx-auto w-full max-w-[88rem] px-5 py-8 sm:px-8" data-deyvo-dashboard-main>
                <x-deyvo::flash />
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
