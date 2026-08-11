@props([
    'title' => null,
])

@php($appName = config('deyvo-core.name', config('app.name', 'Deyvo')))

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-deyvo-core-styles="{{ config('deyvo-core.ui.styles.enabled', true) ? 'enabled' : 'disabled' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' | '.$appName : $appName }}</title>
</head>
<body class="min-h-screen bg-neutral-50 text-neutral-950 antialiased">
    <main class="mx-auto w-full max-w-5xl px-6 py-10">
        <x-deyvo::flash />
        {{ $slot }}
    </main>
</body>
</html>
