@php
    $title = 'Pagina niet gevonden';
    $layout = (string) config('deyvo-core.errors.public_404_layout_view', 'layout.app');
    $section = (string) config('deyvo-core.errors.public_404_layout_section', 'content');
@endphp

@extends($layout)

@section('title', $title)

@section($section)
    @include('deyvo::errors.partials.public-404-content')
@endsection
