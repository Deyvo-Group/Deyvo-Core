@props([
    'type' => 'info',
    'dismissible' => false,
])

@php($styles = [
    'success' => 'border-emerald-200 bg-emerald-50 text-emerald-950',
    'info' => 'border-sky-200 bg-sky-50 text-sky-950',
    'warning' => 'border-amber-200 bg-amber-50 text-amber-950',
    'error' => 'border-red-200 bg-red-50 text-red-950',
])

<div role="alert" {{ $attributes->class([
    'flex items-start justify-between gap-4 rounded-md border px-4 py-3 text-sm',
    $styles[$type] ?? $styles['info'],
]) }}>
    <div class="min-w-0">{{ $slot }}</div>

    @if ($dismissible)
        <button type="button" class="shrink-0 text-lg leading-none" aria-label="Sluiten" data-deyvo-alert-dismiss>&times;</button>
    @endif
</div>
