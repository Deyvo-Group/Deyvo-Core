<section data-deyvo-block="{{ $block['id'] }}" class="py-10">
    <div class="mx-auto max-w-3xl px-6">
        @foreach ($block['data'] as $value)
            @if (is_scalar($value) && filled($value))
                <p class="mt-3 whitespace-pre-line text-base leading-7 text-neutral-700">{{ $value }}</p>
            @endif
        @endforeach
    </div>
</section>
