<?php

namespace FewFar\Sitekit\MonkeyPatch;

use Illuminate\Support\Facades;
use Illuminate\Support\ServiceProvider;
use Statamic\Statamic;

class MonkeyPatchServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // No need to use this method as cannot guarentee order of executions between this and
        // Statamic and the Statamic Eloquent Driver.
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(\Statamic\Sites\Sites::class, Sites::class);
        // $this->app->bind(\Statamic\Fieldtypes\Entries::class, Entries::class);

        $this->removeStaticCachingIfNecessary();
    }

    /**
     * For some unholy reason, statamic.web routes use an atomic lock just to _check_
     * if the request needs to be cached. I have been wondering why many connections
     * were failing in certain contexts...
     */
    protected function removeStaticCachingIfNecessary()
    {
        if (! config('statamic.static_caching.strategy')) {
            return;
        }

        Facades\Route::removeMiddlewareFromGroup('statamic.web', \Statamic\StaticCaching\Middleware\Cache::class);
    }
}
