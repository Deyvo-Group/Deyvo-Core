@php($data = $block['data'])

<section data-deyvo-block="{{ $block['id'] }}" class="border-b border-neutral-200 bg-white py-16 sm:py-24">
    <div class="mx-auto max-w-5xl px-6">
        @if (filled(data_get($data, 'eyebrow')))
            <p class="text-sm font-semibold uppercase tracking-wide text-sky-700">{{ data_get($data, 'eyebrow') }}</p>
        @endif

        @if (filled(data_get($data, 'heading')))
            <h1 class="mt-4 max-w-4xl text-4xl font-semibold text-neutral-950 sm:text-6xl">{{ data_get($data, 'heading') }}</h1>
        @endif

        @if (filled(data_get($data, 'body')))
            <p class="mt-6 max-w-2xl text-lg leading-8 text-neutral-600">{{ data_get($data, 'body') }}</p>
        @endif

        @if (filled(data_get($data, 'action_label')) && filled(data_get($data, 'action_url')))
            <a href="{{ data_get($data, 'action_url') }}" class="mt-8 inline-flex items-center justify-center bg-neutral-950 px-5 py-3 text-sm font-medium text-white transition hover:bg-neutral-800">{{ data_get($data, 'action_label') }}</a>
        @endif
    </div>
</section>
