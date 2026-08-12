@component('deyvo::dashboard.layout', ['title' => 'User bewerken'])
    <div>
        <p class="text-sm font-medium text-sky-700">Users</p>
        <h1 class="mt-1 text-2xl font-semibold text-neutral-950">User bewerken</h1>
        <p class="mt-2 text-sm text-neutral-600">Werk {{ $user->getAttribute('email') }} bij.</p>
    </div>

    @include('deyvo::dashboard.users.form', [
        'action' => route('deyvo.dashboard.users.update', $user->getKey()),
        'method' => 'PUT',
        'submit' => 'Wijzigingen opslaan',
        'user' => $user,
    ])
@endcomponent
