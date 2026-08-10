@props([
    'type' => 'button',
])

<button {{ $attributes->merge([
    'type' => $type,
    'class' => 'inline-flex items-center justify-center rounded-md bg-neutral-950 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-neutral-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-neutral-950 disabled:pointer-events-none disabled:opacity-50',
]) }}>
    {{ $slot }}
</button>
