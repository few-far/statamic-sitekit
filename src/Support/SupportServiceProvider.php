<?php

namespace FewFar\Sitekit\Support;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class SupportServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->configureMigrationsUtility();
        $this->configureEnvironmentLabel();
    }

    protected function configureMigrationsUtility()
    {
        \Statamic\Facades\Utility::extend(function () {
            \Statamic\Facades\Utility::register('migrations')
                ->icon(<<<'HTML'
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8.07143 1C11.9774 1 15.1429 2.43871 15.1429 4.21429C15.1429 5.98986 11.9774 7.42857 8.07143 7.42857C4.16543 7.42857 1 5.98986 1 4.21429C1 2.43871 4.16543 1 8.07143 1Z" stroke="black" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M15.1429 9.35596C15.1429 11.1315 11.9761 12.5702 8.07143 12.5702C4.16671 12.5702 1 11.1328 1 9.35596" stroke="black" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M1 4.21313V14.4988C1 16.2744 4.16671 17.7131 8.07143 17.7131C11.9761 17.7131 15.1429 16.2744 15.1429 14.4988V4.21313" stroke="black" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M6.78564 7.54297V19.6988C6.78564 21.7972 10.5281 23.4975 15.1428 23.4975C19.7574 23.4975 23.4999 21.7972 23.4999 19.6988V7.54297" fill="white"/>
                    <path d="M6.78564 7.54297V19.6988C6.78564 21.7972 10.5281 23.4975 15.1428 23.4975C19.7574 23.4975 23.4999 21.7972 23.4999 19.6988V7.54297" stroke="black" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M23.4999 13.6208C23.4999 15.7193 19.7574 17.4196 15.1428 17.4196C10.5281 17.4196 6.78564 15.7208 6.78564 13.6208" fill="white"/>
                    <path d="M23.4999 13.6208C23.4999 15.7193 19.7574 17.4196 15.1428 17.4196C10.5281 17.4196 6.78564 15.7208 6.78564 13.6208" stroke="black" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M15.1428 3.74561C19.759 3.74561 23.4999 5.4459 23.4999 7.54431C23.4999 9.64271 19.759 11.343 15.1428 11.343C10.5266 11.343 6.78564 9.64271 6.78564 7.54431C6.78564 5.4459 10.5266 3.74561 15.1428 3.74561Z" fill="white" stroke="black" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                HTML)
                ->description('Run `artisan migrate --force` from the Control Panel. Beware, here be dragons.')
                ->action([MigrationController::class, 'view'])
                ->routes(function (Router $router) {
                    $router->post('/', [MigrationController::class, 'run'])->name('run');
                });
        });
    }

    protected function configureEnvironmentLabel()
    {
        $env = app()->environment();
        $status = [
            'staging' => 'status-scheduled',
            'production' => 'status-published',
        ][$env] ?? 'status-working-copy';

        \Statamic\Statamic::inlineScript(<<<JS
            document.addEventListener('alpine:init', () => {
                Statamic.booted(() => {
                    const parent = document.querySelector('.global-header>:first-child');
                    const element = document.createElement('div')
                    element.innerText = '$env';
                    element.classList.add('status-index-field', '$status', 'ml-4', 'text-4xs', 'font-mono');
                    parent.appendChild(element);
                });
            });
        JS);
    }
}
