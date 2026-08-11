@component('deyvo::dashboard.layout', ['title' => 'Activiteit'])
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-sky-700">Dashboard</p>
            <h1 class="mt-1 text-2xl font-semibold text-neutral-950">Activiteit</h1>
            <p class="mt-2 text-sm text-neutral-600">Bekijk wijzigingen, previews en fouten binnen Deyvo Core.</p>
        </div>
    </div>

    <form method="GET" class="mt-8 grid gap-3 sm:grid-cols-[minmax(0,1fr)_14rem_auto]">
        <label class="sr-only" for="deyvo-activity-search">Zoeken</label>
        <input id="deyvo-activity-search" name="q" value="{{ $search }}" placeholder="Zoek gebruiker, onderwerp of request-id">

        <label class="sr-only" for="deyvo-activity-event">Type activiteit</label>
        <select id="deyvo-activity-event" name="event">
            <option value="">Alle activiteiten</option>
            @foreach ($events as $value)
                <option value="{{ $value }}" @selected($event === $value)>{{ \Deyvo\Core\Models\AuditLog::labelFor($value) }}</option>
            @endforeach
        </select>

        <button type="submit" class="inline-flex items-center justify-center rounded-md bg-neutral-950 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-neutral-800">Filteren</button>
    </form>

    @if ($activities->isEmpty())
        <x-deyvo::empty-state class="mt-8" title="Nog geen activiteit" description="Nieuwe dashboardwijzigingen verschijnen hier automatisch." />
    @else
        <div class="mt-6 overflow-x-auto border border-neutral-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-neutral-200 text-left text-sm">
                <thead class="bg-neutral-50 text-xs font-medium uppercase text-neutral-500">
                    <tr>
                        <th class="px-5 py-3">Activiteit</th>
                        <th class="px-5 py-3">Onderwerp</th>
                        <th class="px-5 py-3">Door</th>
                        <th class="px-5 py-3">Moment</th>
                        <th class="px-5 py-3"><span class="sr-only">Details</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200">
                    @foreach ($activities as $activity)
                        <tr>
                            <td class="px-5 py-4 font-medium text-neutral-950">{{ $activity->eventLabel() }}</td>
                            <td class="px-5 py-4 text-neutral-600">{{ $activity->subject_label ?? 'Dashboard' }}</td>
                            <td class="px-5 py-4 text-neutral-600">{{ $activity->actorLabel() }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-neutral-600">{{ $activity->created_at->format('d-m-Y H:i:s') }}</td>
                            <td class="px-5 py-4 text-right"><a href="{{ route('deyvo.dashboard.activity.show', $activity) }}" class="text-sm font-medium text-sky-700 hover:text-sky-900">Details</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $activities->links() }}</div>
    @endif
@endcomponent
