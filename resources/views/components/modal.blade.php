@props([
    'name',
    'title' => null,
    'open' => false,
])

<dialog data-deyvo-modal {{ $attributes->merge(['id' => $name])->class('w-full max-w-lg rounded-md border border-neutral-200 bg-white p-0 text-neutral-950 shadow-xl backdrop:bg-neutral-950/40') }} @if ($open) open @endif>
    <div class="p-6">
        <div class="flex items-start justify-between gap-4">
            @if ($title)
                <h2 class="text-lg font-semibold">{{ $title }}</h2>
            @endif

            <button type="button" class="ml-auto text-xl leading-none text-neutral-500 transition hover:text-neutral-950" aria-label="Sluiten" data-deyvo-modal-close>&times;</button>
        </div>

        <div @class(['mt-5' => $title])>
            {{ $slot }}
        </div>
    </div>
</dialog>
