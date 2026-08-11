@component('deyvo::dashboard.layout', ['title' => 'Pagina toevoegen'])
    <div class="max-w-3xl">
        <div>
            <h1 class="text-2xl font-semibold text-neutral-950">Pagina toevoegen</h1>
            <p class="mt-2 text-sm text-neutral-600">Kies een template en maak een nieuw concept aan.</p>
        </div>

        <form method="GET" action="{{ route('deyvo.dashboard.pages.create') }}" class="mt-8 flex flex-wrap items-end gap-3 border border-neutral-200 bg-white p-5 shadow-sm">
            <div class="min-w-64 flex-1">
                <label for="template-picker" class="block text-sm font-medium text-neutral-900">Template</label>
                <select id="template-picker" name="template" class="mt-2 block w-full rounded-md border-neutral-300 text-sm text-neutral-950 shadow-sm focus:border-neutral-950 focus:ring-neutral-950">
                    @foreach ($templates as $option)
                        <option value="{{ $option['key'] }}" @selected($template['key'] === $option['key'])>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="inline-flex items-center justify-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-900 shadow-sm transition hover:bg-neutral-100">Template laden</button>
        </form>

        @include('deyvo::dashboard.pages.form', [
            'action' => route('deyvo.dashboard.pages.store'),
            'method' => 'POST',
            'page' => null,
            'revision' => null,
            'template' => $template,
        ])
    </div>
@endcomponent
