<?php

namespace Laborb\BunnyStream\Repositories;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VideoRepository
{
    public function fetch(string $video): ?array
    {
        return Cache::rememberForever('bunny:' . $video, function () use ($video) {
            try {
                $result = Http::withHeaders([
                    'Accept' => 'application/json',
                    'AccessKey' => config('statamic.bunny.api_key'),
                ])->get(vsprintf('https://video.bunnycdn.com/library/%s/videos/%s', [
                    config('statamic.bunny.library_id'),
                    $video,
                ]));

                if (!$result->successful()) {
                    throw new \Exception('Unable to find video.');
                }
            } catch (\Throwable $e) {
                Log::error($e->getMessage());
                return null;
            }

            return $result->json();
        });
    }
}
