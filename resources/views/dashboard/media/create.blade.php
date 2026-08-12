@component('deyvo::dashboard.layout', ['title' => 'Media toevoegen'])
    <div>
        <p class="text-sm font-medium text-sky-700">Media</p>
        <h1 class="mt-1 text-2xl font-semibold text-neutral-950">Media toevoegen</h1>
        <p class="mt-2 text-sm text-neutral-600">Upload een bestand of bewaar een externe URL voor de media picker.</p>
    </div>

    @include('deyvo::dashboard.media.form', [
        'action' => route('deyvo.dashboard.media.store'),
        'method' => 'POST',
        'submit' => 'Media opslaan',
    ])
@endcomponent
