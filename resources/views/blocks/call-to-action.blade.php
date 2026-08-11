@php($data = $block['data'])

<section data-deyvo-block="{{ $block['id'] }}" class="border-y border-neutral-200 bg-sky-50 py-14 sm:py-20">
    <div class="mx-auto max-w-3xl px-6 text-center">
        @if (filled(data_get($data, 'heading')))
            <h2 class="text-3xl font-semibold text-neutral-950">{{ data_get($data, 'heading') }}</h2>
        @endif

        @if (filled(data_get($data, 'body')))
            <p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-neutral-700">{{ data_get($data, 'body') }}</p>
        @endif

        @if (filled(data_get($data, 'action_label')) && filled(data_get($data, 'action_url')))
            <a href="{{ data_get($data, 'action_url') }}" class="mt-7 inline-flex items-center justify-center bg-sky-700 px-5 py-3 text-sm font-medium text-white transition hover:bg-sky-800">{{ data_get($data, 'action_label') }}</a>
        @endif
    </div>
</section>
