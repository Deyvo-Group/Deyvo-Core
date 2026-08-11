<form method="POST" action="{{ $action }}" class="mt-8 space-y-6">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <input type="hidden" name="template" value="{{ $template['key'] }}">

    <div class="grid gap-5 border border-neutral-200 bg-white p-5 shadow-sm sm:grid-cols-2">
        <div>
            <label for="page-title" class="block text-sm font-medium text-neutral-900">Titel</label>
            <input id="page-title" type="text" name="title" value="{{ old('title', $revision?->title) }}" required class="mt-2 block w-full rounded-md border-neutral-300 text-sm text-neutral-950 shadow-sm focus:border-neutral-950 focus:ring-neutral-950">
            @error('title')
                <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="page-slug" class="block text-sm font-medium text-neutral-900">Slug</label>
            <input id="page-slug" type="text" name="slug" value="{{ old('slug', $revision?->slug) }}" required class="mt-2 block w-full rounded-md border-neutral-300 text-sm text-neutral-950 shadow-sm focus:border-neutral-950 focus:ring-neutral-950">
            @error('slug')
                <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <p class="text-sm font-medium text-neutral-900">Template</p>
            <p class="mt-1 text-sm text-neutral-600">{{ $template['label'] }}</p>
        </div>
    </div>

    @foreach ($template['sections'] as $section)
        <section class="border border-neutral-200 bg-white p-5 shadow-sm">
            <div>
                <h2 class="text-lg font-semibold text-neutral-950">{{ $section['label'] }}</h2>

                @if ($section['description'])
                    <p class="mt-1 text-sm text-neutral-600">{{ $section['description'] }}</p>
                @endif
            </div>

            <div class="mt-5 space-y-5">
                @foreach ($section['fields'] as $field)
                    @php($path = 'sections.'.$section['key'].'.'.$field['key'])
                    @php($value = old($path, data_get($revision?->sections, $section['key'].'.'.$field['key'])))
                    @php($fieldId = 'deyvo-page-'.$section['key'].'-'.$field['key'])

                    <div>
                        @if ($field['type'] === 'boolean')
                            <label for="{{ $fieldId }}" class="flex items-center gap-3 text-sm font-medium text-neutral-900">
                                <input id="{{ $fieldId }}" type="checkbox" name="sections[{{ $section['key'] }}][{{ $field['key'] }}]" value="1" @checked($value) class="size-4 rounded border-neutral-300 text-neutral-950 focus:ring-neutral-950">
                                <span>{{ $field['label'] }}</span>
                            </label>
                        @else
                            <label for="{{ $fieldId }}" class="block text-sm font-medium text-neutral-900">{{ $field['label'] }}</label>

                            @if ($field['type'] === 'textarea')
                                <textarea id="{{ $fieldId }}" name="sections[{{ $section['key'] }}][{{ $field['key'] }}]" rows="6" placeholder="{{ $field['placeholder'] }}" class="mt-2 block w-full rounded-md border-neutral-300 text-sm text-neutral-950 shadow-sm focus:border-neutral-950 focus:ring-neutral-950">{{ $value }}</textarea>
                            @elseif ($field['type'] === 'select')
                                <select id="{{ $fieldId }}" name="sections[{{ $section['key'] }}][{{ $field['key'] }}]" class="mt-2 block w-full rounded-md border-neutral-300 text-sm text-neutral-950 shadow-sm focus:border-neutral-950 focus:ring-neutral-950">
                                    @if (! $field['required'])
                                        <option value="">Selecteer een optie</option>
                                    @endif

                                    @foreach ($field['options'] as $option)
                                        <option value="{{ $option['value'] }}" @selected($value === $option['value'])>{{ $option['label'] }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input id="{{ $fieldId }}" type="{{ $field['type'] }}" name="sections[{{ $section['key'] }}][{{ $field['key'] }}]" value="{{ $value }}" placeholder="{{ $field['placeholder'] }}" class="mt-2 block w-full rounded-md border-neutral-300 text-sm text-neutral-950 shadow-sm focus:border-neutral-950 focus:ring-neutral-950">
                            @endif
                        @endif

                        @if ($field['help'])
                            <p class="mt-2 text-sm text-neutral-500">{{ $field['help'] }}</p>
                        @endif

                        @error($path)
                            <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach

    <section class="border border-neutral-200 bg-white p-5 shadow-sm">
        <div>
            <h2 class="text-lg font-semibold text-neutral-950">SEO</h2>
            <p class="mt-1 text-sm text-neutral-600">Deze waarden gelden alleen voor deze pagina.</p>
        </div>

        <div class="mt-5 space-y-5">
            <div>
                <label for="seo-title" class="block text-sm font-medium text-neutral-900">Paginatitel</label>
                <input id="seo-title" type="text" name="seo[title]" value="{{ old('seo.title', data_get($revision?->seo, 'title')) }}" class="mt-2 block w-full rounded-md border-neutral-300 text-sm text-neutral-950 shadow-sm focus:border-neutral-950 focus:ring-neutral-950">
                @error('seo.title')
                    <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="seo-description" class="block text-sm font-medium text-neutral-900">Metabeschrijving</label>
                <textarea id="seo-description" name="seo[description]" rows="4" class="mt-2 block w-full rounded-md border-neutral-300 text-sm text-neutral-950 shadow-sm focus:border-neutral-950 focus:ring-neutral-950">{{ old('seo.description', data_get($revision?->seo, 'description')) }}</textarea>
                @error('seo.description')
                    <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <label for="seo-indexable" class="flex items-center gap-3 text-sm font-medium text-neutral-900">
                <input id="seo-indexable" type="checkbox" name="seo[indexable]" value="1" @checked(old('seo.indexable', data_get($revision?->seo, 'indexable', true))) class="size-4 rounded border-neutral-300 text-neutral-950 focus:ring-neutral-950">
                <span>Opneembaar in zoekmachines</span>
            </label>
        </div>
    </section>

    <div class="flex items-center justify-between gap-4">
        <a href="{{ route('deyvo.dashboard.pages.index') }}" class="text-sm font-medium text-neutral-600 hover:text-neutral-950">Annuleren</a>
        <button type="submit" class="inline-flex items-center justify-center rounded-md bg-neutral-950 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-neutral-800">{{ $method === 'POST' ? 'Concept aanmaken' : 'Concept opslaan' }}</button>
    </div>
</form>
