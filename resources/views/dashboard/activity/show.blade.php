@component('deyvo::dashboard.layout', ['title' => 'Activiteit'])
    <div class="max-w-4xl">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-sky-700">Activiteit</p>
                <h1 class="mt-1 text-2xl font-semibold text-neutral-950">{{ $activity->eventLabel() }}</h1>
                <p class="mt-2 text-sm text-neutral-600">{{ $activity->created_at->format('d-m-Y H:i:s') }}</p>
            </div>

            <a href="{{ route('deyvo.dashboard.activity.index') }}" class="text-sm font-medium text-neutral-600 hover:text-neutral-950">Terug naar activiteit</a>
        </div>

        <dl class="mt-8 grid gap-x-8 gap-y-6 border-y border-neutral-200 py-6 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Gebruiker</dt>
                <dd class="mt-1 text-sm text-neutral-950">{{ $activity->actorLabel() }}</dd>
                @if ($activity->actor_email)
                    <dd class="mt-1 text-sm text-neutral-600">{{ $activity->actor_email }}</dd>
                @endif
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Onderwerp</dt>
                <dd class="mt-1 text-sm text-neutral-950">{{ $activity->subject_label ?? 'Dashboard' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Request-id</dt>
                <dd class="mt-1 break-all font-mono text-xs text-neutral-700">{{ $activity->request_id ?? 'Niet beschikbaar' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Request</dt>
                <dd class="mt-1 break-all text-sm text-neutral-950">{{ $activity->method ?? 'CLI' }} {{ $activity->path ?? '' }}</dd>
                @if ($activity->ip_address)
                    <dd class="mt-1 text-sm text-neutral-600">{{ $activity->ip_address }}</dd>
                @endif
            </div>
        </dl>

        <section class="mt-8">
            <h2 class="text-base font-semibold text-neutral-950">Details</h2>
            <pre class="mt-3 overflow-x-auto border border-neutral-200 bg-white p-5 text-xs leading-6 text-neutral-700">{{ json_encode($activity->context ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
        </section>
    </div>
@endcomponent
