@extends('statamic::layout')

@section('title', $title)

@section('content')
    <bunny-overview
        title="{{ $title }}"
        access="{{ $bunny['apiKey'] }}"
        library="{{ $bunny['library'] }}"
        hostname="{{ $bunny['hostname'] }}"
        route-view="{{ $routes['view'] }}"
        route-embed="{{ $routes['embed'] }}"
    ></bunny-overview>

    @if (version_compare(\Statamic\Statamic::version(), '6.0.0', '>='))
        <ui-docs-callout
            topic="{{ $addon['name'] }}"
            url="{{ $addon['url'] }}"
        ></ui-docs-callout>
    @else
        @include('statamic::partials.docs-callout', [
            'topic' => $addon['name'],
            'url' => $addon['url'],
        ])
    @endif
@endsection
