<?php
/**
 * Displays an iframe with am embedded Bunny video.
 *
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
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
        }

        iframe {
            border: 0;
            height: 100%;
            width: 100%;
        }
    </style>
</head>
<body class="flex items-center justify-center h-screen">
    <iframe src="{{ $embedUrl }}"
            loading="lazy"
            allowfullscreen="true"
            allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;"
    ></iframe>
</body>
</html>
