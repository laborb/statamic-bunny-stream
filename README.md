# Bunny Stream

> Bunny Stream is a Statamic add-on that integrates the Bunny Stream API for single stream libraries into the Statamic CP.

## Features

- Upload and rename videos to Bunny Stream.
- Automatically detect transcoding status.
- Delete videos from Bunny Stream.
- Embed videos using a custom field type or custom tag.
- Customize the vidstack.io player to your needs or use your own player.

## Installation

Install the addon using composer:

```bash
composer require laborb/statamic-bunny-stream
```

Insert the required styles & scripts using the following tags:

- Use `{{ bunny:scripts }}` to add all required javascript files. We recommend adding this to the end of the `<body>`.
- Use `{{ bunny:styles }}` to add all required css files. We recommend adding this to the `<head>`.

All inserted files can be configured using the configuration file.

## Configuration

You need to provide the following .env-Variables:

```bash
BUNNY_LIBRARY_ID=yourid            # Your Bunny Stream LibraryID
BUNNY_API_KEY=yourapikey           # Your Libraries API Key
BUNNY_CDN_HOSTNAME=yourcdnhostname # Your Libraries CDN Hostname
```

You can find these values in your Bunny Stream Dashboard at [https://dash.bunny.net/stream/](https://dash.bunny.net/stream/) `Delivery > Stream > API`

You can enable Video embedding by setting the following variables:

```bash
BUNNY_EMBED_PATH="yourpath"
BUNNY_EMBED_DOMAIN="*"
```

All videos will then be available through a direct url. Check the video browser to get the correct url for each video.

Using `BUNNY_EMBED_DOMAIN` you can change the Access Control Header, if you want to limit access.

### Custom CDN Hostname

To add a custom hostname you can do the following:

1. Login to your bunny dashboard and head over to `Delivery > Stream > API`
2. At Pull zone click `Manage`
3. Create a CName entry in your DNS settings pointing to the displayed bunny CDN hostname
4. Enter your custom hostname in the bunny settings and activate SSL
5. Use your custom hostname in the .env `BUNNY_CDN_HOSTNAME=yourcdnhostname`

Now your videos are delivered over your custom hostname.

### Publish Configuration (optional)

After installing the addon you can publish and update the default configuration:

```bash
php artisan vendor:publish --tag=bunny-config
```

### Publish Views (optional)

All views are completely customizable. To publish them use:

```bash
php artisan vendor:publish --tag=bunny-views
```

## Usage

The bunny tag allows you to display a Bunny video stream using the Vidstack player in your Statamic site.

```antlers
{{ bunny id="bunny-video-id" title="My Video" poster="https://example.com/poster.jpg" preload="auto" class="custom-class" tracks="{{ tracks }}" }}
```

### Parameters

| Parameter | Type              | Description                                                                                      |
|-----------|-------------------|--------------------------------------------------------------------------------------------------|
| `id`      | string            | The id of the video you want to display.                                                         |
| `title`   | string (optional) | A title to display for the video. Defaults to `null`.                                            |
| `poster`  | string (optional) | A poster image URL to display before the video starts. Defaults to `null`.                       |
| `width`   | int (optional)    | The width of the video. Defaults to `null`.                                                      |
| `height`  | int (optional)    | The height of the video. Defaults to `null`.                                                     |
| `preload` | string (optional) | How the video should be preloaded. Options: `auto`, `metadata`, `none`. Defaults to `metadata`.  |
| `tracks`  | array (optional)  | An array of additional tracks (e.g., captions, subtitles). Defaults to `[]`.                     |
| `class`   | string (optional) | Additional HTML classes to be added to the wrapper.                                              |

### Configuring Tracks Array

The `tracks` parameter must be formatted as an array of objects, where each object represents a caption or subtitle 
track. The structure follows loosely the [Vidstack track format](https://vidstack.io/docs/wc/player/api/text-tracks/):

Each track object must include the following properties:

- `source`: The URL of the caption file.
- `kind`: The type of track, e.g., `subtitles`, `captions`, `descriptions`.
- `language`: The language code, e.g., `en`, `es`, `fr`.
- `label`: The visible label for the track.

Example JSON configuration for tracks:

```json
[
    { "source": "/path/to/captions-en.vtt", "kind": "subtitles", "language": "en", "label": "English" },
    { "source": "/path/to/captions-es.vtt", "kind": "subtitles", "language": "es", "label": "Spanish" }
]
```

### Bunny-Field & -Fieldset

This addon includes a basic Bunny fieldset that you can use.

When using the fieldset you can use the following markup to output the video player:

```antlers
{{ bunny :id="bunny_video" :poster="bunny_poster[0]" :controls="bunny_controls" :tracks="bunny_captions" }}
```

You can also just use the Bunny field by adding it to any blueprint.

## Customization

You can customize the video player to your needs. 
Check the [vidstack.io](https://vidstack.io/docs/wc/player) documentation for details.
