<?php

namespace FewFar\Sitekit\ViewModels;

use FewFar\Sitekit\Exceptions\AppException;
use FewFar\Sitekit\Imaging\Imaging;
use Statamic\Assets\Asset;

class AssetModel
{
    protected ?Asset $asset;

    public function setAsset(?Asset $asset)
    {
        $this->asset = $asset;

        return $this;
    }

    public function model()
    {
        if (! $this->asset) {
            return null;
        }

        /** @var \Statamic\Assets\Asset|null */
        $mobile_asset = $this->asset->augmentedValue('mobile_asset')->value();
        $dimensions = $this->asset->dimensions();

        return match (true) {
            $this->asset->isVideo() => AppException::unexpected('Videos not yet supported'),
            $this->asset->isSvg(), $this->asset->isImage() => [
                'image' => [
                    ...($this->mapAssetSrc($this->asset) ?? [
                        'src' => $this->asset->url(),
                    ]),
                    'alt' => $this->asset->get('alt'),
                    'width' => $dimensions[0] ?? null,
                    'height' => $dimensions[1] ?? null,
                    // TODO: Merge x-focal-point and data-focus
                    'data-src' => $this->asset->url(),
                    '@error' => '$el.src = $el.dataset.src',
                    'x-focal-point' => str($this->asset->get('focus'))
                        ->explode('-')
                        ->take(2)
                        ->implode(' ') ?: null,
                ],
                'mobile' => ! $mobile_asset ? null : [
                    'src' => $mobile_asset->url(),
                    'alt' => $mobile_asset->get('alt') ?? $this->asset->get('alt'),
                    'width' => ($dimensions = $mobile_asset->dimensions())[0],
                    'height' => $dimensions[1],
                    'x-focal-point' => str($mobile_asset->get('focus'))
                        ->explode('-')
                        ->take(2)
                        ->implode(' ') ?: null
                ],
            ],
            default => null,
        };
    }

    public function mapAssetSrc(Asset $asset)
    {
        $driver = config('domain.imaging.mapper');

        if (! $asset->isImage()) {
            return null;
        }

        if ($driver !== 'local') {
            return null;
        }

        return [
            'src' => '/' . app(Imaging::class)->path($asset),
        ];
    }
}
