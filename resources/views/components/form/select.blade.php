@props([
    'name',
    'label' => null,
])

@php($error = isset($errors) ? $errors->first($name) : null)

<div>
    @if ($label)
        <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-neutral-800">{{ $label }}</label>
    @endif

    <select {{ $attributes->merge([
        'id' => $name,
        'name' => $name,
        'aria-invalid' => $error ? 'true' : 'false',
        'aria-describedby' => $error ? $name.'-error' : null,
    ])->class([
        'block w-full rounded-md border bg-white px-3 py-2 text-sm text-neutral-950 shadow-sm outline-none transition focus:ring-2',
        'border-red-400 focus:border-red-500 focus:ring-red-100' => $error,
        'border-neutral-300 focus:border-neutral-950 focus:ring-neutral-200' => ! $error,
    ]) }}>
        {{ $slot }}
    </select>

    @if ($error)
        <x-deyvo::form.error :message="$error" :id="$name.'-error'" />
    @endif
</div>
