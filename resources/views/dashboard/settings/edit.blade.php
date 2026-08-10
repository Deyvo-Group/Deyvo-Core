@component('deyvo::dashboard.layout', ['title' => 'Instelling bewerken'])
    <div>
        <p class="text-sm font-medium text-sky-700">Instellingen</p>
        <h1 class="mt-1 text-2xl font-semibold text-neutral-950">Instelling bewerken</h1>
        <p class="mt-2 text-sm text-neutral-600">Werk de waarde van {{ $setting->key }} bij.</p>
    </div>

    @include('deyvo::dashboard.settings.form', [
        'action' => route('deyvo.dashboard.settings.update', $setting),
        'method' => 'PUT',
        'submit' => 'Wijzigingen opslaan',
        'setting' => $setting,
    ])
@endcomponent
