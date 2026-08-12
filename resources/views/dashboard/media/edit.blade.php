@component('deyvo::dashboard.layout', ['title' => 'Media bewerken'])
    <div>
        <p class="text-sm font-medium text-sky-700">Media</p>
        <h1 class="mt-1 text-2xl font-semibold text-neutral-950">Media bewerken</h1>
        <p class="mt-2 text-sm text-neutral-600">Werk metadata en vindbaarheid voor {{ $mediaItem->name }} bij.</p>
    </div>

    @include('deyvo::dashboard.media.form', [
        'action' => route('deyvo.dashboard.media.update', $mediaItem),
        'method' => 'PUT',
        'submit' => 'Wijzigingen opslaan',
        'mediaItem' => $mediaItem,
    ])
@endcomponent
