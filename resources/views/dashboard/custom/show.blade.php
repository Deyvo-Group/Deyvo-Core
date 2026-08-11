@component('deyvo::dashboard.layout', ['title' => $page['label']])
    <div class="max-w-3xl">
        <div>
            <h1 class="text-2xl font-semibold text-neutral-950">{{ $page['label'] }}</h1>

            @if ($page['description'])
                <p class="mt-2 text-sm leading-6 text-neutral-600">{{ $page['description'] }}</p>
            @endif
        </div>

        <form method="POST" action="{{ route('deyvo.dashboard.custom.update', ['page' => $page['key']]) }}" class="mt-8 space-y-6">
            @csrf
            @method('PUT')

            <div class="space-y-5 rounded-md border border-neutral-200 bg-white p-5 shadow-sm">
                @foreach ($page['fields'] as $index => $field)
                    @php($fieldId = 'deyvo-dashboard-'.$page['key'].'-'.$index)
                    @php($value = old('values.'.$index, $values[$field['key']] ?? null))

                    <div>
                        @if ($field['type'] === 'boolean')
                            <label for="{{ $fieldId }}" class="flex items-center gap-3 text-sm font-medium text-neutral-900">
                                <input id="{{ $fieldId }}" type="checkbox" name="values[{{ $index }}]" value="1" @checked($value) class="size-4 rounded border-neutral-300 text-neutral-950 focus:ring-neutral-950">
                                <span>{{ $field['label'] }}</span>
                            </label>
                        @else
                            <label for="{{ $fieldId }}" class="block text-sm font-medium text-neutral-900">{{ $field['label'] }}</label>

                            @if ($field['type'] === 'textarea')
                                <textarea id="{{ $fieldId }}" name="values[{{ $index }}]" rows="6" placeholder="{{ $field['placeholder'] }}" class="mt-2 block w-full rounded-md border-neutral-300 text-sm text-neutral-950 shadow-sm focus:border-neutral-950 focus:ring-neutral-950">{{ $value }}</textarea>
                            @elseif ($field['type'] === 'select')
                                <select id="{{ $fieldId }}" name="values[{{ $index }}]" class="mt-2 block w-full rounded-md border-neutral-300 text-sm text-neutral-950 shadow-sm focus:border-neutral-950 focus:ring-neutral-950">
                                    @if (! $field['required'])
                                        <option value="">Selecteer een optie</option>
                                    @endif

                                    @foreach ($field['options'] as $option)
                                        <option value="{{ $option['value'] }}" @selected($value === $option['value'])>{{ $option['label'] }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input id="{{ $fieldId }}" type="{{ $field['type'] }}" name="values[{{ $index }}]" value="{{ $value }}" placeholder="{{ $field['placeholder'] }}" class="mt-2 block w-full rounded-md border-neutral-300 text-sm text-neutral-950 shadow-sm focus:border-neutral-950 focus:ring-neutral-950">
                            @endif
                        @endif

                        @if ($field['help'])
                            <p class="mt-2 text-sm text-neutral-500">{{ $field['help'] }}</p>
                        @endif

                        @error('values.'.$index)
                            <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center justify-center rounded-md bg-neutral-950 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-neutral-800">Opslaan</button>
            </div>
        </form>
    </div>
@endcomponent
