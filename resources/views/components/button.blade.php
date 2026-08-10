@props([
    'type' => 'button',
    'variant' => 'primary',
])

@php($variants = [
    'primary' => 'bg-neutral-950 text-white shadow-sm hover:bg-neutral-800 focus-visible:outline-neutral-950',
    'secondary' => 'border border-neutral-300 bg-white text-neutral-900 shadow-sm hover:bg-neutral-100 focus-visible:outline-neutral-950',
    'danger' => 'bg-red-700 text-white shadow-sm hover:bg-red-800 focus-visible:outline-red-700',
])

<button {{ $attributes->merge(['type' => $type])->class([
    'inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 disabled:pointer-events-none disabled:opacity-50',
    $variants[$variant] ?? $variants['primary'],
]) }}>
    {{ $slot }}
</button>
