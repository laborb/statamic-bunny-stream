<?php
/**
 * Displays a bunny video stream.
 *
 * @var string $id A unique id for the video.
 * @var string $source The source url of the video stream.
 * @var string|null $title A title to display for the image.
 * @var string|null $poster A poster image to display before the video is started / loaded.
 * @var int|null $width The width of the video.
 * @var int|null $height The height of the video.
 * @var string|null $preload How the video should be preloaded. Defaults to 'metadata'.
 * @var array|null $tracks An array of additional tracks that should be added to the video. E.g. captions.
 * @var string|null $class Additional html classes that should be added to the wrapper.
 *
 * @see https://vidstack.io/docs/wc/player
 */
?>

<div id="bunny-{{ $id }}" class="bunny-video {{ $class ?? '' }}"></div>

<script type="module">
    import { VidstackPlayer, VidstackPlayerLayout } from 'https://cdn.vidstack.io/player';

    const player = await VidstackPlayer.create({
        target: '#bunny-{{ $id }}',
        src: '{{ $source }}',
        title: '{{ !empty($title) ? $title : '' }}',
        poster: '{{ !empty($poster) ? $poster : 'null' }}',
        preload: '{{ !empty($preload) ? $preload : 'metadata' }}',
        tracks: {!! !empty($tracks) ? json_encode($tracks) : '[]' !!},
        layout: new VidstackPlayerLayout(),
    });
</script>
