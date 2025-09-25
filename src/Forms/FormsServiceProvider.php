<?php

namespace FewFar\Sitekit\Forms;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class FormsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRoutes();
        $this->configureControlPanel();
        $this->configureEvents();
        $this->configureMigrations();
    }

    protected function configureMigrations()
    {
        $this->publishesMigrations([
            __DIR__.($file = '/migrations/2025_04_24_cms_form_0001_create_submissions_tables.php') => database_path($file),
        ], 'cms-forms');
    }

    protected function configureEvents()
    {
        Event::listen(Events\FormSubmitted::class, function (Events\FormSubmitted $event) {
            SendAdminEmail::dispatch($event->submission, $event->form);
        });
    }

    protected function configureRoutes()
    {
        Route::controller(SubmissionController::class)->group(function () {
            Route::post('/!/submissions', 'store')->name('submissions.store');
        });
    }

    protected function configureControlPanel()
    {
        \Statamic\Statamic::pushCpRoutes(function () {
            Route::controller(SubmissionController::class)->group(function () {
                Route::get('collections/forms/entries/{entry}/submissions', 'index')->name('submissions.index');
                Route::get('collections/forms/entries/{entry}/submissions/{submission}', 'show')->name('submissions.show');
                Route::get('collections/forms/entries/{entry}/submissions/{submission}/email', 'showEmail')->name('submissions/email.show');
            });

            Route::controller(ResendAdminEmailController::class)->group(function () {
                Route::post('/!/submissions/{submission}/notifications', 'store')->name('submissions/notifications.store');
            });
        });

        Fieldtypes\FormSubmissionsLink::register();
    }
}
