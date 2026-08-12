@component('deyvo::dashboard.layout', ['title' => 'Menu bewerken'])
    <div>
        <p class="text-sm font-medium text-sky-700">Menu’s</p>
        <h1 class="mt-1 text-2xl font-semibold text-neutral-950">Menu bewerken</h1>
        <p class="mt-2 text-sm text-neutral-600">Werk {{ $menu->title }} bij.</p>
    </div>

    @include('deyvo::dashboard.menus.form', [
        'action' => route('deyvo.dashboard.menus.update', $menu),
        'method' => 'PUT',
        'submit' => 'Wijzigingen opslaan',
        'menu' => $menu,
    ])
@endcomponent
