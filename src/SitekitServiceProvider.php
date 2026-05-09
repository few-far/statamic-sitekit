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
        $this->registerConfig();
        $this->registerServiceProviders();
    }

    public function registerConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/sitekit.php', 'sitekit');
    }

    public function registerServiceProviders()
    {
        $this->app->register(MonkeyPatch\MonkeyPatchServiceProvider::class);
        $this->app->register(Redirects\RedirectServiceProvider::class);
        $this->app->register(Forms\FormsServiceProvider::class);
        $this->app->register(Analytics\AnalyticsServiceProvider::class);
        $this->app->register(ViewModels\ViewModelsServiceProvider::class);
        $this->app->register(Imaging\ImagingServiceProvider::class);
        $this->app->register(Support\SupportServiceProvider::class);
        $this->app->register(SocialShare\SocialShareServiceProvider::class);
    }
}
