# Video Stream GDPR Compliant (aka Bunny Stream)

Statamic addon for managing one Bunny Stream library in the Control Panel and rendering its videos with Vidstack.

## Features

- Browse, search, and paginate the configured Bunny Stream library in the Statamic Control Panel.
- Support privacy-focused, GDPR/DSGVO-compliant video implementations without addon-side cookies, advertising, related-video integrations, or visitor tracking.
- Upload videos with resumable Uppy/TUS uploads.
- Edit video titles, descriptions, and custom metadata.
- Upload VTT/SRT captions and choose thumbnails from Statamic image assets.
- Delete videos with a two-step confirmation.
- Select videos through a Bunny fieldtype or the included reusable fieldset.
- Render Bunny HLS streams with Vidstack, optional posters and text tracks, or in background mode.
- Optionally expose share and iframe URLs with configurable CSP `frame-ancestors` restrictions.
- Use the same addon release with Statamic 5 and Statamic 6.

## GDPR/DSGVO Compliance

The addon is designed to support GDPR/DSGVO-compliant video implementations. It uses direct Bunny Stream HLS delivery with a Vidstack player instead of Bunny's iframe player. The addon itself does not add cookies, advertising, related-video integrations, or visitor tracking.

Bunny states that Bunny Stream and Bunny Player do not set cookies or transmit personally identifiable information for playback and analytics. Anonymous technical telemetry and CDN performance data may still be sent to Bunny endpoints.

Actual compliance depends on the complete project setup, including the Bunny data processing agreement, storage and replication regions, logging settings, legal basis, privacy notice, and whether the default Vidstack CDN assets are used or self-hosted. Review [Bunny's Data and Privacy documentation](https://docs.bunny.net/stream/data-and-privacy) and [GDPR information](https://bunny.net/gdpr/) for each project.

Installing this addon alone cannot guarantee legal compliance. If there is any doubt, have the implementation reviewed by your data protection officer (`Datenschutzbeauftragte/r`) or qualified privacy counsel before using it in production.

## Bunny Account

A Bunny.net account and one Stream library are required. You can use our [affiliate link](https://bunny.net?ref=uhvsqhaw0n) to support development at no extra cost.

## Compatibility

- PHP `^8.2`
- Statamic `^5.0 || ^6.0`
- The host application must satisfy the PHP and Laravel requirements of its installed Statamic version.

The addon ships separate Control Panel bundles for Vue 2/Statamic 5 and Vue 3/Statamic 6. The matching bundle is selected automatically; the current Statamic 6 UI remains unchanged.

## Installation

```bash
composer require laborb/statamic-bunny-stream
```

Statamic publishes the Control Panel assets during Composer installation. If a deployment skips Composer scripts, publish them manually:

```bash
php artisan vendor:publish --tag=statamic-bunny-stream --force
```

## Configuration

Add the Stream library credentials to `.env`:

```dotenv
BUNNY_LIBRARY_ID=your-library-id
BUNNY_API_KEY=your-library-api-key
BUNNY_CDN_HOSTNAME=your-library-cdn-hostname
```

The values are available in the API section of the Stream library. See [Bunny's Stream dashboard documentation](https://docs.bunny.net/stream/dashboard).

The library API key is delivered to authenticated Control Panel JavaScript because video management and uploads call Bunny directly. Treat Control Panel access as privileged. Never render this key in public templates, commit it, or expose it in logs.

### Player Assets

Add these tags once to the frontend layout that renders `{{ bunny }}`:

```antlers
<head>
    {{ bunny:styles }}
</head>
<body>
    <!-- Page content -->
    {{ bunny:scripts }}
</body>
```

By default, these tags load Vidstack JavaScript and CSS from `cdn.vidstack.io`. Publish the config to change or self-host these assets.

### Public Playback And Embedding

Public playback and iframe routes are disabled by default. Enable them with a route prefix:

```dotenv
BUNNY_EMBED_PATH=/videos
BUNNY_EMBED_DOMAIN="'self' https://www.example.com"
```

This creates:

- `/videos/{video-id}`: public video page
- `/videos/{video-id}/embed`: iframe document

`BUNNY_EMBED_DOMAIN` becomes the iframe response's CSP `frame-ancestors` source list. The default `*` permits every embedding origin. Use valid CSP source expressions, including the quotes around `'self'` when needed.

These routes do not add authentication or generate Bunny playback tokens. Access restrictions for the HLS URLs must be configured separately in Bunny and your application.

### Custom CDN Hostname

1. Open the pull zone linked to the Stream library.
2. Open `General > Hostnames` and add the hostname.
3. Copy the displayed CNAME target into the DNS provider. Disable proxying while Bunny verifies the record.
4. Select `Verify & Activate SSL` in Bunny.
5. Set `BUNNY_CDN_HOSTNAME` to the verified hostname.

See [Bunny's current custom hostname guide](https://docs.bunny.net/cdn/custom-hostname) for provider-specific details.

### Publish Configuration

```bash
php artisan vendor:publish --tag=bunny-config
```

The published file is `config/statamic/bunny.php`.

### Publish Views

```bash
php artisan vendor:publish --tag=bunny-views
```

This publishes the player, public playback, and iframe views to `resources/views/vendor/bunny`.

## Usage

Render a video by its Bunny video ID:

```antlers
{{ bunny
    id="bunny-video-id"
    title="My Video"
    poster="https://example.com/poster.jpg"
    preload="metadata"
    class="custom-video"
    :tracks="tracks"
}}
```

The addon requests `https://{BUNNY_CDN_HOSTNAME}/{id}/playlist.m3u8` and passes that HLS source to Vidstack.

### Parameters

| Parameter | Type | Description |
| --- | --- | --- |
| `id` | string | Required Bunny video ID. |
| `title` | string | Optional accessible video title. |
| `poster` | string | Optional poster image URL. |
| `controls` | boolean | Shows the Vidstack default layout when `true` (default). When `false`, the player uses muted autoplay, loop, and inline playback for background video. |
| `preload` | string | Optional Vidstack preload value, such as `auto`, `metadata`, or `none`. |
| `tracks` | array | Optional Vidstack text-track objects or caption rows from the included fieldset. |
| `class` | string | Optional classes added to the player wrapper. |

### Text Tracks

Direct track arrays use Vidstack's text-track properties:

```json
[
    {
        "src": "/captions/en.vtt",
        "kind": "captions",
        "srclang": "en",
        "label": "English",
        "default": true
    }
]
```

Common `kind` values include `captions`, `subtitles`, `descriptions`, `chapters`, and `metadata`. See the [Vidstack text-track documentation](https://vidstack.io/docs/wc/player/api/text-tracks/) for supported formats and properties.

The included Statamic fieldset instead exposes `source`, `kind`, `language`, and `label` fields. The addon converts those field values to Vidstack's `src` and `srclang` properties automatically.

### Bunny Field And Fieldset

Add the Bunny fieldtype to any blueprint to store a video ID. The included `bunny_video` fieldset also provides poster, controls, and caption fields:

```antlers
{{ bunny :id="bunny_video" :poster="bunny_poster[0]" :controls="bunny_controls" :tracks="bunny_captions" }}
```

### Public Video Metadata

Metadata routes are disabled by default. To enable a read-only endpoint:

```dotenv
BUNNY_METADATA_PATH=/api/bunny/videos
```

`GET /api/bunny/videos/{video-id}` accepts Bunny UUIDs and returns only `id`,
`title`, `description`, `alt`, and `captions` (only `srclang`, `label`, `version`).
It never returns arbitrary custom metadata, the library API key, or the raw Bunny
API response. The editor's `description` meta tag takes precedence over Bunny's
top-level description. An optional custom `alt` meta tag provides a short
alternative text. Subtitle files remain on the Bunny CDN at
`https://{hostname}/{video-id}/captions/{srclang}.vtt`. Metadata is cached server-side for five minutes and
responses for one minute. Failed requests return a generic `503` response.

Existing fields still store and return video ID strings. Player tags, embedding
routes, and the existing player cache retain their interfaces. Enabling metadata
does not change existing content or make the Bunny field return an object.

## Development

Install frontend dependencies, then build both compatibility bundles:

```bash
npm install
npm run build
```

Targeted commands are available during development:

```bash
npm run build:v5
npm run build:v6
npm run dev
```

`npm run dev` targets the Statamic 6/Vue 3 bundle. `npm run build` must pass before publishing because it creates both production bundles.

Both production bundles use IIFE scope because Statamic loads them as classic scripts. This keeps dependency helpers such as `encodeURI` from shadowing browser globals and breaking other control-panel components (for example focal-point previews).

## Customization

Publish the views for markup changes and the config for player asset changes. See the [Vidstack player documentation](https://vidstack.io/docs/wc/player) for its API and styling options.

## Disclaimer

This addon is an independent project and is not affiliated with, endorsed by, or sponsored by Bunny.net. Users are responsible for complying with Bunny.net's terms and applicable legal requirements.

## Support

Report bugs and feature requests in the [GitHub issue tracker](https://github.com/laborb/statamic-bunny-stream/issues), or email [support@laborb.de](mailto:support@laborb.de).
