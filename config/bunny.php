<?php
return [
    // The unique identifier for your Bunny Stream library.
    // This is required to interact with the Bunny Stream API and manage your videos.
    'library_id' => env('BUNNY_LIBRARY_ID'),

    // The hostname for your Bunny CDN, used for streaming videos.
    // Typically, this is a custom domain or a Bunny.net-provided URL.
    'hostname' => env('BUNNY_CDN_HOSTNAME'),

    // The API key for authenticating requests to the Bunny Stream API.
    // This should be kept secure and not exposed in frontend code.
    'api_key' => env('BUNNY_API_KEY'),

    // Embedding video is disabled by default. Add a path to enable this feature.
    'embedding' => [
        // The relative path where embedded videos will be accessed.
        // This is useful for defining the structure of embed URLs.
        'path' => env('BUNNY_EMBED_PATH'),

        // The allowed domain for embedding videos.
        // The default value of '*' allows embedding on any domain.
        'domain' => env('BUNNY_EMBED_DOMAIN', '*'),
    ],

    // External JavaScript libraries required for video playback.
    // The vidstack.io player script is included by default.
    // You can add your own js files here or replace the files.
    'scripts' => [
        'https://cdn.vidstack.io/player',
    ],

    // External CSS stylesheets required for video player styling.
    // Includes the default video and theme styles from vidstack.io.
    // You can add your own css files here or replace the files.
    'styles' => [
        'https://cdn.vidstack.io/player/video.css',
        'https://cdn.vidstack.io/player/theme.css',
    ],
];
