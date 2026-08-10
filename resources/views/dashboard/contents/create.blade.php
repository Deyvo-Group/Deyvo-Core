@component('deyvo::dashboard.layout', ['title' => 'Content toevoegen'])
    <div>
        <p class="text-sm font-medium text-sky-700">Content</p>
        <h1 class="mt-1 text-2xl font-semibold text-neutral-950">Content toevoegen</h1>
        <p class="mt-2 text-sm text-neutral-600">Gebruik een stabiele sleutel om dit blok in de website op te halen.</p>
    </div>

    @include('deyvo::dashboard.contents.form', [
        'action' => route('deyvo.dashboard.contents.store'),
        'method' => 'POST',
        'submit' => 'Content opslaan',
    ])
@endcomponent
