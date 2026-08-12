@component('deyvo::dashboard.layout', ['title' => 'Menu’s'])
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-sky-700">Dashboard</p>
            <h1 class="mt-1 text-2xl font-semibold text-neutral-950">Menu’s</h1>
            <p class="mt-2 text-sm text-neutral-600">Beheer navigatiestructuren die publieke Blade-views via Core kunnen uitlezen.</p>
        </div>

        <a href="{{ route('deyvo.dashboard.menus.create') }}" class="inline-flex items-center justify-center rounded-md bg-neutral-950 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-neutral-800">Menu toevoegen</a>
    </div>

    @if ($menus->isEmpty())
        <x-deyvo::empty-state class="mt-8" title="Nog geen menu’s" description="Maak bijvoorbeeld een header- of footermenu.">
            <a href="{{ route('deyvo.dashboard.menus.create') }}" class="inline-flex items-center justify-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-900 shadow-sm transition hover:bg-neutral-100">Menu toevoegen</a>
        </x-deyvo::empty-state>
    @else
        <div class="mt-8 overflow-x-auto border border-neutral-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-neutral-200 text-left text-sm">
                <thead class="bg-neutral-50 text-xs font-medium uppercase text-neutral-500">
                    <tr>
                        <th class="px-5 py-3">Sleutel</th>
                        <th class="px-5 py-3">Titel</th>
                        <th class="px-5 py-3">Items</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"><span class="sr-only">Acties</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200">
                    @foreach ($menus as $menu)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-4 font-mono text-xs text-neutral-600">{{ $menu->key }}</td>
                            <td class="px-5 py-4 font-medium text-neutral-950">{{ $menu->title }}</td>
                            <td class="px-5 py-4 text-neutral-600">{{ count($menu->items ?? []) }}</td>
                            <td class="px-5 py-4"><x-deyvo::badge :variant="$menu->is_active ? 'success' : 'neutral'">{{ $menu->is_active ? 'Actief' : 'Uit' }}</x-deyvo::badge></td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-3">
                                    <a href="{{ route('deyvo.dashboard.menus.edit', $menu) }}" class="text-sm font-medium text-sky-700 hover:text-sky-900">Bewerken</a>
                                    <form method="POST" action="{{ route('deyvo.dashboard.menus.destroy', $menu) }}">
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

        <div class="mt-5">{{ $menus->links() }}</div>
    @endif
@endcomponent
