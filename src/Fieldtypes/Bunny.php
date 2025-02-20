<?php

namespace Laborb\BunnyStream\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Bunny extends Fieldtype
{
    protected static $title = 'Bunny';
    protected $icon = 'video';

    public function preload(): array
    {
        return [
            'api' => config('statamic.bunny.api_key'),
            'library' => config('statamic.bunny.library_id'),
            'hostname' => config('statamic.bunny.hostname'),
        ];
    }
}
