@component('deyvo::dashboard.layout', ['title' => 'SEO'])
    <div>
        <p class="text-sm font-medium text-sky-700">Dashboard</p>
        <h1 class="mt-1 text-2xl font-semibold text-neutral-950">SEO</h1>
        <p class="mt-2 text-sm text-neutral-600">Beheer globale SEO-defaults; pagina’s kunnen deze per revisie overschrijven.</p>
    </div>

    <form method="POST" action="{{ route('deyvo.dashboard.seo.update') }}" class="mt-8 max-w-3xl space-y-6 border-t border-neutral-300 pt-6">
        @csrf
        @method('PUT')

        <x-deyvo::form.input name="seo[title]" label="Standaard paginatitel" :value="$seo['title']" />
        <x-deyvo::form.textarea name="seo[description]" label="Standaard metabeschrijving" :value="$seo['description']" />
        <x-deyvo::form.input name="seo[canonical_url]" type="url" label="Canonieke URL" :value="$seo['canonical_url']" />
        <x-deyvo::form.input name="seo[og_image]" type="url" label="Social image" :value="$seo['og_image']" />
        <x-deyvo::form.checkbox name="seo[indexable]" label="Indexeer website" :checked="$seo['indexable']" />

        <div class="flex flex-wrap items-center gap-3">
            <x-deyvo::button type="submit">SEO opslaan</x-deyvo::button>
        </div>
    </form>
@endcomponent
