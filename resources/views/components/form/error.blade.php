@props([
    'message',
])

<p {{ $attributes->class('mt-1.5 text-sm text-red-700') }}>
    {{ $message }}
</p>
