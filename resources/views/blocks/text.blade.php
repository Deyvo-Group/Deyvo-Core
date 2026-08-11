@php($data = $block['data'])

<section data-deyvo-block="{{ $block['id'] }}" class="py-14 sm:py-20">
    <div class="mx-auto max-w-3xl px-6">
        @if (filled(data_get($data, 'heading')))
            <h2 class="text-3xl font-semibold text-neutral-950">{{ data_get($data, 'heading') }}</h2>
        @endif

        @if (filled(data_get($data, 'body')))
            <div class="mt-5 whitespace-pre-line text-base leading-7 text-neutral-700">{{ data_get($data, 'body') }}</div>
        @endif
    </div>
</section>
