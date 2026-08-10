@props([
    'title',
    'description' => null,
])

<section {{ $attributes->class('rounded-md border border-dashed border-neutral-300 bg-white px-6 py-12 text-center') }}>
    <h2 class="text-base font-semibold text-neutral-950">{{ $title }}</h2>

    @if ($description)
        <p class="mx-auto mt-2 max-w-md text-sm text-neutral-600">{{ $description }}</p>
    @endif

    @if (trim($slot))
        <div class="mt-5">{{ $slot }}</div>
    @endif
</section>
