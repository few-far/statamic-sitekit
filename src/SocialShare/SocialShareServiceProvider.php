<?php

namespace FewFar\Sitekit\SocialShare;

use Illuminate\Support\Facades;
use Illuminate\Support\ServiceProvider;

class SocialShareServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->registerListeners();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        $this->configureCommands();
        $this->configureRoutes();
    }

    /**
     * Register module event listeners.
     */
    protected function registerListeners()
    {
        if (! config('sitekit.social_share.enabled')) {
            return;
        }

        Facades\Event::subscribe(HandleImageCleanup::class);
    }

    /**
     * Register console commands.
     */
    protected function configureCommands()
    {
        $this->commands([
            CleanupCommand::class,
        ]);
    }

    /**
     * Register routes for the module.
     */
    protected function configureRoutes()
    {
        if (! config('sitekit.social_share.enabled')) {
            return;
        }

        Facades\Route::middleware('web')->group(function () {
            Facades\Route::get('/!/social-share/entries/{id}/social-share.webp', ImageController::class)
                ->unless(app()->isLocal())
                ->middleware('signed')
                ->name('sitekit.social-share');

            Facades\Route::get('/!/social-share/entries/{id}/social-share', HtmlController::class)
                ->unless(app()->isLocal())
                ->middleware('signed')
                ->name('sitekit.social-share.render');
        });
    }
}
