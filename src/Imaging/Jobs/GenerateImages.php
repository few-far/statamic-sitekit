<?php

namespace FewFar\Sitekit\Imaging\Jobs;

use FewFar\Sitekit\Imaging\Imaging;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Statamic\Assets\Asset;
use Statamic\Facades;

class GenerateImages implements ShouldQueue
{
    use Queueable;

    public string $asset;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset->id();
    }

    public function handle(Imaging $imaging)
    {
        $asset = Facades\Asset::find($this->asset);

        if (! $asset) {
            report('GenerateAsset scheduled to make a crop, but unable to find asset: ' . $this->asset);

            return;
        }

        if (! $imaging->shouldHandle($asset)) {
            return;
        }

        $this->removeMemoryLimit();

        $imaging->generate($asset);
    }

    protected function removeMemoryLimit()
    {
        ini_set('memory_limit', -1);
    }
}
