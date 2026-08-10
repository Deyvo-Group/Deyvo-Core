<section {{ $attributes->class('overflow-hidden rounded-md border border-neutral-200 bg-white shadow-sm') }}>
    @isset($header)
        <header class="border-b border-neutral-200 px-5 py-4">
            {{ $header }}
        </header>
    @endisset

    <div class="px-5 py-4">
        {{ $slot }}
    </div>

    @isset($footer)
        <footer class="border-t border-neutral-200 px-5 py-4">
            {{ $footer }}
        </footer>
    @endisset
</section>
