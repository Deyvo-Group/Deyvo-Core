@component('deyvo::dashboard.layout', ['title' => 'Instellingen'])
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-sky-700">Dashboard</p>
            <h1 class="mt-1 text-2xl font-semibold text-neutral-950">Instellingen</h1>
            <p class="mt-2 text-sm text-neutral-600">Beheer eenvoudige, sitebrede sleutel-waarde instellingen.</p>
        </div>

        <a href="{{ route('deyvo.dashboard.settings.create') }}" class="inline-flex items-center justify-center rounded-md bg-neutral-950 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-neutral-800">Instelling toevoegen</a>
    </div>

    @if ($settings->isEmpty())
        <x-deyvo::empty-state class="mt-8" title="Nog geen instellingen" description="Voeg een eerste sitebrede waarde toe.">
            <a href="{{ route('deyvo.dashboard.settings.create') }}" class="inline-flex items-center justify-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-900 shadow-sm transition hover:bg-neutral-100">Instelling toevoegen</a>
        </x-deyvo::empty-state>
    @else
        <div class="mt-8 overflow-x-auto border border-neutral-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-neutral-200 text-left text-sm">
                <thead class="bg-neutral-50 text-xs font-medium uppercase text-neutral-500">
                    <tr>
                        <th scope="col" class="px-5 py-3">Sleutel</th>
                        <th scope="col" class="px-5 py-3">Waarde</th>
                        <th scope="col" class="px-5 py-3">Bijgewerkt</th>
                        <th scope="col" class="px-5 py-3"><span class="sr-only">Acties</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200">
                    @foreach ($settings as $setting)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-4 font-mono text-xs text-neutral-600">{{ $setting->key }}</td>
                            <td class="max-w-md truncate px-5 py-4 text-neutral-700">{{ $setting->value }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-neutral-600">{{ $setting->updated_at->diffForHumans() }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-3">
                                    <a href="{{ route('deyvo.dashboard.settings.edit', $setting) }}" class="text-sm font-medium text-sky-700 hover:text-sky-900">Bewerken</a>
                                    <form method="POST" action="{{ route('deyvo.dashboard.settings.destroy', $setting) }}">
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

        <div class="mt-5">{{ $settings->links() }}</div>
    @endif
@endcomponent
