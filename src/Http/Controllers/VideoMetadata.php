<?php

namespace Laborb\BunnyStream\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Laborb\BunnyStream\Repositories\VideoRepository;

class VideoMetadata
{
    public function __invoke(string $video, VideoRepository $repository): JsonResponse
    {
        $data = $repository->fetchMetadata($video);

        if (empty($data)) {
            return response()->json(['message' => 'Video metadata unavailable.'], 503)
                ->header('Cache-Control', 'no-store');
        }

        return response()->json([
            'id' => $video,
            'title' => trim($data['title'] ?? ''),
            'description' => trim(collect($data['metaTags'] ?? [])->firstWhere('property', 'description')['value'] ?? $data['description'] ?? ''),
            'alt' => trim(collect($data['metaTags'] ?? [])->firstWhere('property', 'alt')['value'] ?? ''),
            'captions' => collect($data['captions'] ?? [])->map(fn (array $caption) => [
                'srclang' => $caption['srclang'],
                'label' => $caption['label'] ?? $caption['srclang'],
                'version' => $caption['version'] ?? 0,
            ])->values()->all(),
        ])->header('Cache-Control', 'public, max-age=60');
    }
}
