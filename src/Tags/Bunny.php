<?php

namespace Laborb\BunnyStream\Tags;

use Illuminate\Contracts\View\View;
use Statamic\Fields\Values;
use Statamic\Tags\Tags;

class Bunny extends Tags
{
    protected static $handle = 'bunny';

    public function index(): View|string
    {
        $video = $this->params->get('id');
        if (empty($video)) {
            return __('Please provide a valid video id.');
        }

        $hostname = config('statamic.bunny.hostname');
        if (empty($hostname)) {
            return __('Please add a valid hostname to the configuration.');
        }

        return view('bunny::tags.video', [
            'id' => $video,
            'source' => sprintf('https://%s/%s/playlist.m3u8', $hostname, $video),
            'title' => $this->params->get('title'),
            'width' => $this->params->get('width'),
            'height' => $this->params->get('height'),
            'poster' => $this->params->get('poster'),
            'controls' => $this->params->get('controls', true),
            'tracks' => collect($this->params->get('tracks', []))->map(function ($track, $index) {
                $values = $track;
                if (is_object($track) && get_class($track) === Values::class) {
                    $data = $track->toArray();
                    $values = [
                        'kind' => $data['kind']->__toString() ?? 'captions',
                        'src' => $data['source']->value()?->get()?->toArray()[0]['url'] ?? '',
                        'srclang' => $data['language']->__toString(),
                        'label' => $data['label']->__toString(),
                        'default' => $index === 0,
                    ];
                }

                return $values;
            })->toArray(),
            'preload' => $this->params->get('preload'),
            'class' => $this->params->get('class'),
        ]);
    }

    public function scripts(): string
    {
        return collect(config('statamic.bunny.scripts', []))->map(function (string $script) {
            return sprintf('<script src="%s" type="module"></script>', $script);
        })->join('');
    }

    public function styles(): string
    {
        return collect(config('statamic.bunny.styles', []))->map(function (string $stylesheet) {
            return sprintf('<link href="%s" rel="stylesheet" />', $stylesheet);
        })->join('');
    }
}
