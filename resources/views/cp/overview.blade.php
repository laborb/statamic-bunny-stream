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

    @include('statamic::partials.docs-callout', [
        'topic' => $addon['name'],
        'url' => $addon['url'],
    ])
@endsection
