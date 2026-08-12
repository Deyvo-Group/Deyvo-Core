@component('deyvo::dashboard.layout', ['title' => 'User toevoegen'])
    <div>
        <p class="text-sm font-medium text-sky-700">Users</p>
        <h1 class="mt-1 text-2xl font-semibold text-neutral-950">User toevoegen</h1>
        <p class="mt-2 text-sm text-neutral-600">Maak een gebruiker aan via het Laravel user-model.</p>
    </div>

    @include('deyvo::dashboard.users.form', [
        'action' => route('deyvo.dashboard.users.store'),
        'method' => 'POST',
        'submit' => 'User opslaan',
    ])
@endcomponent
