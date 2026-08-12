@php($user = $user ?? null)

<form method="POST" action="{{ $action }}" class="mt-8 max-w-3xl space-y-6 border-t border-neutral-300 pt-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-deyvo::form.input name="name" label="Naam" :value="$user?->getAttribute('name')" required />
    <x-deyvo::form.input name="email" type="email" label="E-mail" :value="$user?->getAttribute('email')" required />
    <x-deyvo::form.input name="password" type="password" label="Wachtwoord" />

    <div class="flex flex-wrap items-center gap-3">
        <x-deyvo::button type="submit">{{ $submit }}</x-deyvo::button>
        <a href="{{ route('deyvo.dashboard.users.index') }}" class="text-sm font-medium text-neutral-600 hover:text-neutral-950">Annuleren</a>
    </div>
</form>
