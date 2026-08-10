@props([
    'variant' => 'neutral',
])

@php($variants = [
    'neutral' => 'bg-neutral-100 text-neutral-700',
    'success' => 'bg-emerald-100 text-emerald-800',
    'info' => 'bg-sky-100 text-sky-800',
    'warning' => 'bg-amber-100 text-amber-800',
    'danger' => 'bg-red-100 text-red-800',
])

<span {{ $attributes->class([
    'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium',
    $variants[$variant] ?? $variants['neutral'],
]) }}>
    {{ $slot }}
</span>
