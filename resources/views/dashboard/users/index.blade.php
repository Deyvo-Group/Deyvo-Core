@component('deyvo::dashboard.layout', ['title' => 'Users'])
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-sky-700">Dashboard</p>
            <h1 class="mt-1 text-2xl font-semibold text-neutral-950">Users</h1>
            <p class="mt-2 text-sm text-neutral-600">Beheer gebruikers via het geconfigureerde Laravel user-model.</p>
        </div>

        @if ($userModel !== null)
            <a href="{{ route('deyvo.dashboard.users.create') }}" class="inline-flex items-center justify-center rounded-md bg-neutral-950 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-neutral-800">User toevoegen</a>
        @endif
    </div>

    @if ($userModel === null)
        <x-deyvo::empty-state class="mt-8" title="Geen user-model geconfigureerd" description="Stel auth.providers.users.model of DEYVO_USERS_MODEL in." />
    @elseif ($error)
        <x-deyvo::empty-state class="mt-8" title="Users kunnen niet worden geladen" :description="$error" />
    @elseif ($users->isEmpty())
        <x-deyvo::empty-state class="mt-8" title="Nog geen users" description="Maak de eerste dashboardgebruiker aan.">
            <a href="{{ route('deyvo.dashboard.users.create') }}" class="inline-flex items-center justify-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-900 shadow-sm transition hover:bg-neutral-100">User toevoegen</a>
        </x-deyvo::empty-state>
    @else
        <div class="mt-8 overflow-x-auto border border-neutral-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-neutral-200 text-left text-sm">
                <thead class="bg-neutral-50 text-xs font-medium uppercase text-neutral-500">
                    <tr>
                        <th class="px-5 py-3">Naam</th>
                        <th class="px-5 py-3">E-mail</th>
                        <th class="px-5 py-3">Bijgewerkt</th>
                        <th class="px-5 py-3"><span class="sr-only">Acties</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-5 py-4 font-medium text-neutral-950">{{ $user->getAttribute('name') }}</td>
                            <td class="px-5 py-4 text-neutral-600">{{ $user->getAttribute('email') }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-neutral-600">{{ $user->getAttribute('updated_at')?->diffForHumans() }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-3">
                                    <a href="{{ route('deyvo.dashboard.users.edit', $user->getKey()) }}" class="text-sm font-medium text-sky-700 hover:text-sky-900">Bewerken</a>
                                    <form method="POST" action="{{ route('deyvo.dashboard.users.destroy', $user->getKey()) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-medium text-red-700 hover:text-red-900">Verwijderen</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $users->links() }}</div>
    @endif
@endcomponent
