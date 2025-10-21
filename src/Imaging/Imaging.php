<?php

namespace FewFar\Sitekit\Imaging;

use Illuminate\Support\Facades\File;
use Intervention\Image\Constraint;
use Intervention\Image\ImageManager;
use Statamic\Contracts\Assets\Asset;

class Imaging
{
    public function generate(Asset $asset)
    {
        if (! $this->shouldHandle($asset)) {
            return;
        }

        $config = $this->config($asset);

        $image = $this->manager()
            ->make($asset->resolvedPath())
            ->encode($config['encode'], $config['quality']);

        $resized = $image->getSize()->resize($config['width'], $config['height'], function (Constraint $constraint) {
            $constraint->aspectRatio();
        });

        $image->getCore()->resizeImage($resized->getWidth(), $resized->getHeight(), \Imagick::FILTER_CATROM, $blur = 1);

        $path = tap($this->realPath($this->path($asset, $config)), function ($path) {
            File::ensureDirectoryExists(dirname($path));
        });

        $image->save($path);
    }

    public function cleanup(Asset $asset)
    {
        if (! $this->shouldHandle($asset)) {
            return;
        }

        File::deleteDirectory($this->realPath($this->folder($asset)));
    }

    public function config(Asset $asset)
    {
        $max = min(3000, max($asset->dimensions()));

        return [
            'width' => $max,
            'height' => $max,
            'encode' => 'webp',
            'quality' => 85,
            'filename' => $asset->filename(),
        ];
    }

    public function folder(Asset $asset)
    {
        $source = strtr(':container/:path/', [
            ':container' => $asset->container()->handle(),
            ':path' => $asset->path(),
        ]);

        return preg_replace('~/+~', '/', $source);
    }

    public function path(Asset $asset, ?array $config = null)
    {
        $config ??= $this->config($asset);
        $path = strtr(':hash/:file.:format', [
            ':hash' => md5(json_encode($config)),
            ':file' => $config['filename'],
            ':format' => $config['encode'],
        ]);

        return preg_replace('~/+~', '/', $this->folder($asset) . $path);
    }

    public function realPath(string $path)
    {
        return preg_replace('~/+~', '/', storage_path('app/imaging/' . $path));
    }

    public function manager()
    {
        return new ImageManager(['driver' => 'imagick']);
    }

    /**
     * @param  Asset|mixed  $asset
     */
    public function shouldHandle($asset)
    {
        if (! $asset instanceof Asset) {
            return false;
        }

        return $asset->isImage();
    }
}
