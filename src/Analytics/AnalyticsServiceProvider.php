<?php

namespace FewFar\Sitekit\Analytics;

use Illuminate\Support\ServiceProvider;

class AnalyticsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureMigrations();
    }

    /**
     * Provide migrations.
     */
    protected function configureMigrations() : void
    {
        $this->publishesMigrations([
            __DIR__.($file = '/migrations/2025_05_13_cms_analytics_0001_create_analytics_logs_table.php') => database_path($file),
        ], 'cms-analytics');
    }
}
