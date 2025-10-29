<?php

namespace FewFar\Sitekit\Redirects;

use FewFar\Sitekit\Redirects\HandlesNotFoundExceptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades;
use Illuminate\Support\ServiceProvider;
use Statamic\Exceptions\NotFoundHttpException;

class RedirectServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureMigrations();
        $this->configureNotFoundHandler();
        $this->configureControlPanel();
    }

    protected function configureNotFoundHandler()
    {
        NotFoundHttpException::renderUsing(function () {
            return app(HandlesNotFoundExceptions::class)->handle($this);
        });
    }

    protected function configureMigrations()
    {
        $this->publishesMigrations([
            __DIR__.($file = '/migrations/2025_04_22_cms_redirects_0001_create_redirect_tables.php') => database_path($file),
        ], 'cms-redirects');
    }

    protected function configureControlPanel()
    {
        \Statamic\Facades\CP\Nav::extend(function ($nav) {
            $nav->tools('Redirects')
                ->route('redirects')
                ->icon('git')
                ->active('redirect')
                ->can('manage redirects');
        });

        \Statamic\Statamic::pushCpRoutes(function () {
            Facades\Route::get('redirects', [RedirectController::class, 'view'])->name('redirects');
            Facades\Route::get('redirects/create', [RedirectController::class, 'create'])->name('redirects.create');
            Facades\Route::post('redirects', [RedirectController::class, 'store'])->name('redirects.store');
            Facades\Route::get('redirects/{id}', [RedirectController::class, 'edit'])->name('redirects.edit');
            Facades\Route::post('redirects/{id}', [RedirectController::class, 'update'])->name('redirects.update');
            Facades\Route::delete('redirects/{id}', [RedirectController::class, 'destroy'])->name('redirects.delete');
        });

        $this->app->booted(function () {
            \Statamic\Facades\Permission::register('manage redirects')
                ->label('Manage Redirects');
        });
    }
}
