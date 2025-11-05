<?php

namespace FewFar\Sitekit\ViewModels;

use FewFar\Sitekit\ViewModels\Listeners\CreatePageModel;
use FewFar\Sitekit\ViewModels\Listeners\DumpMatchedRoute;
use FewFar\Sitekit\ViewModels\Listeners\PreparePublishFormFields;
use Illuminate\Routing\Events\PreparingResponse;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades;
use Illuminate\Support\ServiceProvider;
use Statamic\Statamic;

class ViewModelsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Fieldtypes\Cta::register();

        Facades\View::composer('statamic::entries.create', PreparePublishFormFields::class);

        Facades\Event::listen(PreparingResponse::class, CreatePageModel::class);
        Facades\Event::listen(RouteMatched::class, DumpMatchedRoute::class);
    }
}
