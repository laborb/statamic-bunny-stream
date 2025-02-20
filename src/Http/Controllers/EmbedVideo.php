<?php

namespace Laborb\BunnyStream\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laborb\BunnyStream\Repositories\VideoRepository;

class EmbedVideo
{
    private VideoRepository $repository;

    public function __construct(VideoRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(Request $request, string $video): Response
    {
        $hostname = config('statamic.bunny.hostname');
        if (empty($hostname)) {
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Bunny hostname not found.');
        }

        $videoData = $this->repository->fetch($video);
        if (empty($videoData)) {
            abort(Response::HTTP_NOT_FOUND,  'Unable to retrieve video data.');
        }

        $domain = config('statamic.bunny.embedding.domain');
        return response(view('bunny::embed-video', [
            'id' => $video,
            'videoData' => $videoData,
            'source' => sprintf('https://%s/%s/playlist.m3u8', $hostname, $video),
            'poster' => sprintf('https://%s/%s/%s', $hostname, $video, $videoData['thumbnailFileName']),
            'embedUrl' => route('bunny.embedVideo', $video),
        ]))->header('Content-Security-Policy', "frame-ancestors $domain");
    }
}
