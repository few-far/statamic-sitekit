<?php

namespace FewFar\Sitekit\Imaging\Actions;

use FewFar\Sitekit\Imaging\Imaging;
use FewFar\Sitekit\Imaging\Jobs\GenerateImages;
use Statamic\Actions\Action;
use Statamic\Contracts\Assets\Asset;

class GenerateImagesAction extends Action
{
    public static function title()
    {
        return __('Regenerate Images');
    }

    public function visibleTo($item)
    {
        return $item instanceof Asset && $item->isImage();
    }

    public function authorize($user, $item)
    {
        return $user->can('store', [Asset::class, $item->container()]);
    }

    public function run($items, $values)
    {
        $imaging = app(Imaging::class);

        foreach ($items as $item) {
            GenerateImages::dispatchIf($imaging->shouldHandle($item), $item);
        }
    }
}
