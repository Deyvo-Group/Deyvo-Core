@component('deyvo::dashboard.layout', ['title' => 'Pagina niet gevonden'])
    <section class="rounded-md border border-neutral-200 bg-white px-6 py-8 shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-wide text-sky-700">404</p>
        <h1 class="mt-3 text-2xl font-semibold text-neutral-950">Verdwaald in het dashboard.</h1>
        <p class="mt-3 max-w-2xl text-sm text-neutral-600">
            Deze beheerpagina bestaat niet, of is inmiddels naar een andere plek verhuisd.
        </p>

        <blockquote class="mt-6 max-w-2xl rounded-md border border-dashed border-neutral-300 bg-neutral-50 px-5 py-4 text-sm font-medium text-neutral-700">
            "Route kwijt. De beheerder in mij wijst naar de cache, maar fluistert: misschien was ik het."
        </blockquote>

        <dl class="mt-6 grid gap-3 text-sm sm:grid-cols-2">
            <div class="rounded-md border border-neutral-200 bg-neutral-50 px-4 py-3">
                <dt class="font-medium text-neutral-500">Gezocht pad</dt>
                <dd class="mt-1 break-all font-mono text-neutral-900">{{ request()->path() }}</dd>
            </div>
            <div class="rounded-md border border-neutral-200 bg-neutral-50 px-4 py-3">
                <dt class="font-medium text-neutral-500">Dashboard pad</dt>
                <dd class="mt-1 font-mono text-neutral-900">{{ $dashboardPath ?: '-' }}</dd>
            </div>
        </dl>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('deyvo.dashboard.index') }}" class="inline-flex items-center justify-center rounded-md bg-neutral-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-neutral-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-neutral-950">
                Naar dashboard
            </a>
            <a href="{{ url()->previous() }}" class="inline-flex items-center justify-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-semibold text-neutral-900 transition hover:bg-neutral-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-neutral-950">
                Terug
            </a>
        </div>
    </section>
@endcomponent
