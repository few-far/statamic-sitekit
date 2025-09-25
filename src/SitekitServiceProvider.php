<?php

namespace FewFar\Sitekit;

use Illuminate\Support\ServiceProvider;

class SitekitServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(MonkeyPatch\MonkeyPatchServiceProvider::class);
        $this->app->register(Redirects\RedirectServiceProvider::class);
        $this->app->register(Forms\FormsServiceProvider::class);
        $this->app->register(Analytics\AnalyticsServiceProvider::class);
        $this->app->register(ViewModels\ViewModelsServiceProvider::class);
        $this->app->register(Imaging\ImagingServiceProvider::class);
        $this->app->register(Support\SupportServiceProvider::class);
    }
}
