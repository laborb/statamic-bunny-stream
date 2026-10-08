<?php

use Illuminate\Support\Facades\Route;
use Laborb\BunnyStream\Http\Controllers\VideoMetadata;

$metadataPath = config('statamic.bunny.metadata_path');
if (!empty($metadataPath)) {
    Route::get(rtrim($metadataPath, '/') . '/{video}', VideoMetadata::class)
        ->whereUuid('video')
        ->middleware(['api', 'throttle:120,1'])
        ->name('bunny.videoMetadata');
}
