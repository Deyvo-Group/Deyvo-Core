@props([
    'title' => 'Onderhoud',
    'message' => 'Deze applicatie is tijdelijk niet beschikbaar.',
])

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | {{ config('deyvo-core.name') }}</title>
</head>
<body class="min-h-screen bg-neutral-50 text-neutral-950 antialiased">
    <main class="mx-auto flex min-h-screen w-full max-w-xl items-center px-6 py-12">
        <section class="w-full rounded-md border border-neutral-200 bg-white p-8 shadow-sm">
            <p class="text-sm font-medium text-sky-700">{{ config('deyvo-core.name') }}</p>
            <h1 class="mt-3 text-2xl font-semibold">{{ $title }}</h1>
            <p class="mt-3 text-sm leading-6 text-neutral-600">{{ $message }}</p>
        </section>
    </main>
</body>
</html>
