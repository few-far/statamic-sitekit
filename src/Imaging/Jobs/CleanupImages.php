<?php

namespace FewFar\Sitekit\Imaging\Jobs;

use FewFar\Sitekit\Imaging\Imaging;
use Illuminate\Foundation\Bus\Dispatchable;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades;

class CleanupImages
{
    use Dispatchable;

    public string $asset;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset->id();
    }

    public function handle(Imaging $imaging)
    {
        $asset = Facades\Asset::find($this->asset);

        if (! $asset) {
            report('Scheduled to cleanup images, but unable to find asset: ' . $this->asset);

            return;
        }

        if (! $imaging->shouldHandle($asset)) {
            return;
        }

        $imaging->cleanup($asset);
    }
}
