<?php
/**
 * Displays the player for a given Bunny video.
 *
 * @var string $id ID of the Bunny video.
 * @var string $source Url to the video stream.
 * @var string $poster Url to the video's poster image.
 * @var string $embedUrl Url for embedding this video.
 * @var array $videoData Detailed video data as returned by the Bunny API.
 *
 * @see https://docs.bunny.net/reference/video_getvideo
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta property="og:url" content="{{ $embedUrl }}" />
    <meta property="og:image" content="{{ $poster }}" />
    <meta property="og:image:secure_url" content="{{ $poster }}" />
    <meta property="og:site_name" content="{{ config('app.name') }}" />
    <meta property="og:title" content="{{ $videoData['title'] }}" />
    <meta property="og:type" content="video.other" />

    <meta property="og:video:url" content="{{ $source }}" />
    <meta property="og:video:secure_url" content="{{ $source }}" />

    <meta property="og:video:type" content="text/html" />
    <meta property="og:video:width" content="{{ $videoData['width'] }}">
    <meta property="og:video:height" content="{{ $videoData['height'] }}">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="player">
    <meta property="twitter:domain" content="{{ request()->root() }}">
    <meta property="twitter:url" content="{{ $embedUrl }}">
    <meta name="twitter:title" content="{{ $videoData['title'] }}">
    <meta name="twitter:image" content="{{ $poster }}">
    <meta name="twitter:player" content="{{ $embedUrl }}">
    <meta name="twitter:player:width" content="{{ $videoData['width'] }}">
    <meta name="twitter:player:height" content="{{ $videoData['height'] }}">

    <title>{{ $videoData['title'] }} - {{ config('app.name') }}</title>

    <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "VideoObject",
          "name": "{{ $videoData['title'] }}",
          "description": "",
          "thumbnailUrl": "{{ $poster }}",
          "embedUrl": "{{ $embedUrl }}"
        }
    </script>

    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: black;
            display: flex;
            align-items: center;
            justify-items: center;
        }

        .bunny-video {
            height: 100%;
            width: auto;
            margin: 0 auto;
        }
    </style>

    {!! Statamic::tag('bunny:styles') !!}
</head>
<body>
{!!
    Statamic::tag('bunny')->params([
        'id' => $id,
        'title' => $videoData['title'],
        'poster' => $poster,
    ])
 !!}

{!! Statamic::tag('bunny:scripts') !!}
</body>
</html>
