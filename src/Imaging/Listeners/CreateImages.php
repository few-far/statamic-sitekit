<?php

namespace FewFar\Sitekit\Imaging\Listeners;

use FewFar\Sitekit\Imaging\Imaging;
use FewFar\Sitekit\Imaging\Jobs\GenerateImages;
use Statamic\Events\AssetReuploaded;
use Statamic\Events\AssetUploaded;

class CreateImages
{
    public function __construct(protected Imaging $imaging)
    {
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function handle(AssetUploaded|AssetReuploaded $event)
    {
        if (! $this->imaging->shouldHandle($event->asset)) {
            return;
        }

        GenerateImages::dispatch($event->asset);
    }
}
