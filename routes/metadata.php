<?php

use Illuminate\Support\Facades\Route;
use Laborb\BunnyStream\Http\Controllers\VideoMetadata;

$metadataPath = config('statamic.bunny.metadata_path');
if (!empty($metadataPath)) {
    // Metadata must not consume the host application's API quota.
    Route::get(rtrim($metadataPath, '/') . '/{video}', VideoMetadata::class)
        ->whereUuid('video')
        ->middleware('throttle:120,1,bunny-metadata:')
        ->name('bunny.videoMetadata');
}
