@component('deyvo::dashboard.layout', ['title' => 'Menu toevoegen'])
    <div>
        <p class="text-sm font-medium text-sky-700">Menu’s</p>
        <h1 class="mt-1 text-2xl font-semibold text-neutral-950">Menu toevoegen</h1>
        <p class="mt-2 text-sm text-neutral-600">Maak een beheerde navigatie voor publieke templates.</p>
    </div>

    @include('deyvo::dashboard.menus.form', [
        'action' => route('deyvo.dashboard.menus.store'),
        'method' => 'POST',
        'submit' => 'Menu opslaan',
    ])
@endcomponent
