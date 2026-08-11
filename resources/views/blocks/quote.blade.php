@php($data = $block['data'])

<section data-deyvo-block="{{ $block['id'] }}" class="py-14 sm:py-20">
    <figure class="mx-auto max-w-3xl border-l-4 border-sky-700 pl-6">
        @if (filled(data_get($data, 'quote')))
            <blockquote class="text-2xl font-medium leading-9 text-neutral-900">{{ data_get($data, 'quote') }}</blockquote>
        @endif

        @if (filled(data_get($data, 'author')))
            <figcaption class="mt-5 text-sm font-medium text-neutral-600">{{ data_get($data, 'author') }}</figcaption>
        @endif
    </figure>
</section>
