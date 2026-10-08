<?php

namespace Laborb\BunnyStream\Repositories;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VideoRepository
{
    public function fetch(string $video): ?array
    {
        return Cache::rememberForever('bunny:' . $video, fn () => $this->request($video));
    }

    public function fetchMetadata(string $video): ?array
    {
        return Cache::remember('bunny:metadata:' . config('statamic.bunny.library_id') . ':' . $video, 300,
            fn () => $this->request($video, 5));
    }

    private function request(string $video, ?int $timeout = null): ?array
    {
        try {
            $request = Http::withHeaders([
                'Accept' => 'application/json',
                'AccessKey' => config('statamic.bunny.api_key'),
            ]);

            if ($timeout !== null) {
                $request->connectTimeout(3)->timeout($timeout);
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
    }
}
