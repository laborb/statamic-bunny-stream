<?php

namespace Laborb\BunnyStream;

use Laborb\BunnyStream\Tags\Bunny;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Facades\CP\Nav;

class ServiceProvider extends AddonServiceProvider
{
    protected $viewNamespace = 'bunny';

    protected $scripts = [
        __DIR__ . '/../resources/dist/addon.js',
    ];

    protected $stylesheets = [
        __DIR__ . '/../resources/dist/addon.css',
    ];

    /*
    protected $vite = [
        'input' => [
            'resources/js/addon.js',
            'resources/css/addon.css'
        ],
        'publicDirectory' => 'resources/dist',
    ];
    */

    protected $routes = [
        'cp' => __DIR__ . '/../routes/cp.php',
        'web' => __DIR__ . '/../routes/web.php',
    ];

    protected $tags = [
        Bunny::class,
    ];

    public function bootAddon(): void
    {
        Nav::extend(function ($nav) {
            $nav->content(__('Video Browser'))
                ->section('Content')
                ->route('bunny.cp.videoBrowser')
                ->icon('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" /></svg>');
        });

        Fieldtypes\Bunny::register();

        $this->mergeConfigFrom(__DIR__ . '/../config/bunny.php','statamic.bunny');
        $this->loadJsonTranslationsFrom(__DIR__ . '/../lang');

        $this->publishes([
            __DIR__ . '/../config/bunny.php' => config_path('statamic/bunny.php'),
        ],'bunny-config');

        $this->publishes([
            __DIR__ . '/../resources/views/tags/video.blade.php' => resource_path('views/vendor/bunny/tags/video.blade.php'),
            __DIR__ . '/../resources/views/embed-video.blade.php' => resource_path('views/vendor/bunny/embed-video.blade.php'),
            __DIR__ . '/../resources/views/view-video.blade.php' => resource_path('views/vendor/bunny/view-video.blade.php'),
        ],'bunny-views');
    }
}
