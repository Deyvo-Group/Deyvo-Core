@props([
    'page',
])

@foreach (deyvo_blocks($page) as $block)
    @includeFirst([
        'deyvo-blocks.'.$block['type'],
        'deyvo::blocks.'.$block['type'],
        'deyvo::blocks.fallback',
    ], ['block' => $block])
@endforeach
