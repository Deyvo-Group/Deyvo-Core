@component('deyvo::layouts.app', ['title' => 'Deyvo Core'])
    <section class="space-y-6">
        <div class="space-y-3">
            <p class="text-sm font-medium text-sky-700">Deyvo Core</p>
            <h1 class="text-3xl font-semibold text-neutral-950">Deyvo Core werkt</h1>
            <p class="max-w-2xl text-base text-neutral-600">
                Deze view gebruikt de gedeelde layout en button component uit de Deyvo Core package.
            </p>
        </div>

        <x-deyvo::button>
            Opslaan
        </x-deyvo::button>
    </section>
@endcomponent
