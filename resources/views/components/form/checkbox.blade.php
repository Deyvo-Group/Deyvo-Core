@props([
    'name',
    'label' => null,
    'value' => '1',
    'checked' => false,
])

<label {{ $attributes->class('inline-flex items-center gap-2 text-sm text-neutral-800') }}>
    <input type="checkbox" name="{{ $name }}" value="{{ $value }}" @checked(old($name, $checked)) class="size-4 rounded border-neutral-300 text-neutral-950 focus:ring-neutral-200">

    @if ($label)
        <span>{{ $label }}</span>
    @else
        <span>{{ $slot }}</span>
    @endif
</label>
