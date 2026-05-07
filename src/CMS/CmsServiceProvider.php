<?php

namespace FewFar\Sitekit\Cms;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Statamic\CP\Navigation\Nav;

class CmsServiceProvider extends EventServiceProvider
{
    protected $resources = [
        'resources/js/cp.js',
        'resources/css/cp.css',
    ];

    public function register()
    {
    }

    public function boot(): void
    {
        $this->configureControlPanelNav();
        $this->configureControlPanelAssets();
    }

    protected function configureControlPanelNav()
    {
        \Statamic\Facades\CP\Nav::extend(function (Nav $nav) {
            $nav->remove('Top Level');

            $nav->findOrCreate('Content', 'Collections')
                ->route('collections.show', 'pages');

            $nav->findOrCreate('Content', 'Globals')
                ->route('globals.update', 'site_settings');
        });
    }


    protected function configureControlPanelAssets()
    {
        $resources = collect($this->resources)
            ->filter(base_path(...));
        if ($resources->isEmpty()) {
            return;
        }

        \Statamic\Statamic::inlineScript(<<<'JS'
            const s = document.createElement( 'script' );
            s.setAttribute( 'src', 'https://cdn.tailwindcss.com');
            document.body.appendChild( s );
            s.addEventListener('load', () => {
                tailwind.config = {
                    prefix: '~',
                    corePlugins: {
                        preflight: false,
                    },
                }
            })
        JS);

        \Statamic\Statamic::vite('app', $resources->all());
    }

}
