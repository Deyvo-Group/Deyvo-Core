@component('deyvo::dashboard.layout', ['title' => 'Content bewerken'])
    <div>
        <p class="text-sm font-medium text-sky-700">Content</p>
        <h1 class="mt-1 text-2xl font-semibold text-neutral-950">Content bewerken</h1>
        <p class="mt-2 text-sm text-neutral-600">Werk de inhoud en publicatiestatus van {{ $content->key }} bij.</p>
    </div>

    @include('deyvo::dashboard.contents.form', [
        'action' => route('deyvo.dashboard.contents.update', $content),
        'method' => 'PUT',
        'submit' => 'Wijzigingen opslaan',
        'content' => $content,
    ])
@endcomponent
