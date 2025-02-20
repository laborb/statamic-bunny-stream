<?php

use Illuminate\Support\Facades\Route;
use Laborb\BunnyStream\Http\Controllers\Cp\FetchAssets;
use Laborb\BunnyStream\Http\Controllers\Cp\Overview;

Route::get('/bunny/videos', Overview::class)->name('bunny.cp.videoBrowser');
Route::get('/bunny/assets', FetchAssets::class)->name('bunny.cp.fetchAssets');
