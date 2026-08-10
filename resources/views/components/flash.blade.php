@foreach (['success', 'info', 'warning', 'error'] as $type)
    @foreach ((array) session('deyvo.flash.'.$type, []) as $message)
        <x-deyvo::alert :type="$type" class="mb-4" :dismissible="true">
            {{ $message }}
        </x-deyvo::alert>
    @endforeach
@endforeach
