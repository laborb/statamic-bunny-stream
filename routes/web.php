<?php

use Illuminate\Support\Facades\Route;
use Laborb\BunnyStream\Http\Controllers\EmbedVideo;
use Laborb\BunnyStream\Http\Controllers\ViewVideo;

$embed_path = config('statamic.bunny.embedding.path');
if (!empty($embed_path)) {
    Route::name('bunny.')->group(function () use ($embed_path) {
        Route::get($embed_path . '/{video}', ViewVideo::class)->name('viewVideo');
        Route::get($embed_path . '/{video}/embed', EmbedVideo::class)->name('embedVideo');
    });
}
