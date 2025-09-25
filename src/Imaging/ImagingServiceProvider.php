<?php

namespace FewFar\Sitekit\Imaging;

use FewFar\Sitekit\Imaging\Jobs\GenerateImages;
use Illuminate\Support\Facades;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Statamic\Events;
use Statamic\Facades\Asset;

class ImagingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureCommands();

        if (config('domain.imaging.optimise') !== 'local') {
            return;
        }

        $this->configureListeners();
        $this->configureActions();
    }

    protected function configureCommands()
    {
        /** @param \Illuminate\Console\Command $this */
        Artisan::command('cms:imaging:generate', function () {
            /** @var \Illuminate\Console\Command */
            $command = $this;

            foreach (Asset::all()->sortBy->id() as $asset) {
                $command->info('Generating: ' . $asset->id());

                GenerateImages::dispatch($asset);
            }
        });
    }

    protected function configureListeners()
    {
        Facades\Event::listen(Events\AssetUploaded::class, Listeners\CreateImages::class);
        Facades\Event::listen(Events\AssetDeleted::class, Listeners\RemoveImages::class);
        Facades\Event::listen(Events\AssetReuploaded::class, Listeners\RemoveImages::class);
        Facades\Event::listen(Events\AssetReuploaded::class, Listeners\CreateImages::class);
    }

    protected function configureActions()
    {
        Actions\GenerateImagesAction::register();
    }
}
