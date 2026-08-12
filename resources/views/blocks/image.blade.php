@php($data = $block['data'])
@php($url = deyvo_media_url(data_get($data, 'media')))

@if ($url)
    <figure data-deyvo-block="{{ $block['id'] }}" class="bg-white py-12 sm:py-16">
        <div class="mx-auto max-w-5xl px-6">
            <img src="{{ $url }}" alt="{{ data_get($data, 'alt', '') }}" class="w-full rounded-md object-cover">

            @if (filled(data_get($data, 'caption')))
                <figcaption class="mt-3 text-sm text-neutral-500">{{ data_get($data, 'caption') }}</figcaption>
            @endif
        </div>
    </figure>
@endif
