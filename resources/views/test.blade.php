@component('deyvo::layouts.app', ['title' => 'Deyvo Core'])
    <section class="space-y-8">
        <div class="space-y-3">
            <p class="text-sm font-medium text-sky-700">Deyvo Core</p>
            <h1 class="text-3xl font-semibold text-neutral-950">Deyvo Core werkt</h1>
            <p class="max-w-2xl text-base text-neutral-600">
                Deze view gebruikt de gedeelde layout, UI-componenten en health endpoint uit de Deyvo Core package.
            </p>
        </div>

        <x-deyvo::alert type="success" :dismissible="true">
            Deyvo Core is geladen en de basiscomponenten zijn beschikbaar.
        </x-deyvo::alert>

        <div class="flex flex-wrap items-center gap-3">
            <x-deyvo::button>Opslaan</x-deyvo::button>
            <x-deyvo::button variant="secondary">Annuleren</x-deyvo::button>
            <x-deyvo::badge variant="success">Actief</x-deyvo::badge>
            <a href="{{ route('deyvo.health') }}" class="text-sm font-medium text-sky-700 hover:text-sky-900">Health endpoint</a>
        </div>

        <x-deyvo::card>
            <x-slot:header>
                <h2 class="text-base font-semibold text-neutral-950">Gedeeld formulier</h2>
            </x-slot:header>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-deyvo::form.input name="name" label="Naam" placeholder="Deyvo" />
                <x-deyvo::form.select name="language" label="Taal">
                    <option value="nl">Nederlands</option>
                    <option value="en">English</option>
                </x-deyvo::form.select>
            </div>

            <x-slot:footer>
                <x-deyvo::form.checkbox name="enabled" label="Ingeschakeld" :checked="true" />
            </x-slot:footer>
        </x-deyvo::card>

        <x-deyvo::empty-state title="Nog geen items" description="Voeg een eerste item toe om te beginnen.">
            <x-deyvo::button variant="secondary">Item toevoegen</x-deyvo::button>
        </x-deyvo::empty-state>
    </section>
@endcomponent
