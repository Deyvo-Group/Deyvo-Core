@props([
    'title' => 'Dashboard',
])

@php($appName = config('deyvo-core.name', config('app.name', 'Deyvo')))
@php($navigation = app(\Deyvo\Core\Dashboard\DashboardManager::class)->navigation())

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | {{ $appName }}</title>
</head>
<body class="min-h-screen bg-neutral-100 text-neutral-950 antialiased">
    <div class="min-h-screen md:grid md:grid-cols-[16rem_minmax(0,1fr)]">
        <aside class="hidden bg-neutral-950 px-4 py-5 text-neutral-200 md:block">
            <a href="{{ route('deyvo.dashboard.index') }}" class="block px-3 text-lg font-semibold text-white">{{ $appName }}</a>
            <p class="mt-1 px-3 text-xs font-medium text-neutral-400">Dashboard</p>

            <nav class="mt-8 space-y-1" aria-label="Dashboard navigatie">
                @foreach ($navigation as $item)
                    <a href="{{ route($item['route']) }}" @class([
                        'block rounded-md px-3 py-2 text-sm font-medium transition',
                        'bg-white/10 text-white' => request()->routeIs($item['active']),
                        'text-neutral-300 hover:bg-white/5 hover:text-white' => ! request()->routeIs($item['active']),
                    ])>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </aside>

        <div class="min-w-0">
            <header class="border-b border-neutral-200 bg-white">
                <div class="mx-auto flex min-h-16 max-w-7xl items-center gap-4 px-5 sm:px-8">
                    <a href="{{ route('deyvo.dashboard.index') }}" class="text-base font-semibold text-neutral-950 md:hidden">{{ $appName }}</a>

                    <nav class="flex min-w-0 flex-1 items-center gap-4 overflow-x-auto md:hidden" aria-label="Dashboard navigatie">
                        @foreach ($navigation as $item)
                            <a href="{{ route($item['route']) }}" @class([
                                'whitespace-nowrap text-sm font-medium',
                                'text-neutral-950' => request()->routeIs($item['active']),
                                'text-neutral-500 hover:text-neutral-950' => ! request()->routeIs($item['active']),
                            ])>{{ $item['label'] }}</a>
                        @endforeach
                    </nav>

                    <p class="ml-auto hidden text-sm text-neutral-500 sm:block">{{ config('deyvo-core.version') }}</p>
                </div>
            </header>

            <main class="mx-auto w-full max-w-7xl px-5 py-8 sm:px-8">
                <x-deyvo::flash />
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
