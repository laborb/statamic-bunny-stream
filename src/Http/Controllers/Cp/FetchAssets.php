<?php

namespace Laborb\BunnyStream\Http\Controllers\Cp;

use Illuminate\Http\JsonResponse;
use Statamic\Facades\AssetContainer;
use Statamic\Http\Controllers\CP\CpController;

class FetchAssets extends CpController
{
    public function __invoke(): JsonResponse
    {
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'webp'];
        $assets = [];

        $containers = AssetContainer::all();
        foreach ($containers as $container) {
            foreach ($container->assets() as $asset) {
                $extension = strtolower($asset->extension());
                if (in_array($extension, $allowedExtensions)) {
                    $assets[] = [
                        'label' => $asset->basename(),
                        'url' => $asset->absoluteUrl(),
                    ];
                }
            }
        }

        return response()->json([
            'items' => $assets,
        ]);
    }
}
