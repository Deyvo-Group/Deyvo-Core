@component('deyvo::dashboard.layout', ['title' => 'Instelling toevoegen'])
    <div>
        <p class="text-sm font-medium text-sky-700">Instellingen</p>
        <h1 class="mt-1 text-2xl font-semibold text-neutral-950">Instelling toevoegen</h1>
        <p class="mt-2 text-sm text-neutral-600">Gebruik een stabiele sleutel die je elders in de website kunt uitlezen.</p>
    </div>

    @include('deyvo::dashboard.settings.form', [
        'action' => route('deyvo.dashboard.settings.store'),
        'method' => 'POST',
        'submit' => 'Instelling opslaan',
    ])
@endcomponent
