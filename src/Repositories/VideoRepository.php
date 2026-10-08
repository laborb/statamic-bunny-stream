<?php

namespace Laborb\BunnyStream\Repositories;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VideoRepository
{
    public function fetch(string $video, ?int $ttl = null): ?array
    {
        $fetch = function () use ($video, $ttl) {
            try {
                $request = Http::withHeaders([
                    'Accept' => 'application/json',
                    'AccessKey' => config('statamic.bunny.api_key'),
                ]);

                if ($ttl !== null) {
                    $request->connectTimeout(3)->timeout(5);
                }

                $result = $request->get(vsprintf('https://video.bunnycdn.com/library/%s/videos/%s', [
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
        };

        // Preserve the existing player cache; metadata needs a bounded, library-specific cache.
        return $ttl === null
            ? Cache::rememberForever('bunny:' . $video, $fetch)
            : Cache::remember('bunny:metadata:' . config('statamic.bunny.library_id') . ':' . $video, $ttl, $fetch);
    }
}
