<?php

namespace FewFar\Sitekit\Imaging\Listeners;

use FewFar\Sitekit\Imaging\Imaging;
use FewFar\Sitekit\Imaging\Jobs\CleanupImages;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetReuploaded;

class RemoveImages
{
    public function __construct(protected Imaging $imaging)
    {
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function handle(AssetDeleted|AssetReuploaded $event)
    {
        if (! $this->imaging->shouldHandle($event->asset)) {
            return;
        }

        // Might seen pointless to dispatch a job, but just incase there are tasks in the queue
        // to generate images for this asset we want this to happen after them.
        CleanupImages::dispatch($event->asset);
    }
}
