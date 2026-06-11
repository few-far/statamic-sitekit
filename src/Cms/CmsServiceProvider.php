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
        $this->configureControlPanelTailwind();
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

        \Statamic\Statamic::vite('app', $resources->all());
    }

    protected function configureControlPanelTailwind()
    {
        \Statamic\Statamic::inlineScript(<<<'JS'
            (function () {
                const style = document.createElement('style');
                style.setAttribute('type', 'text/tailwindcss');
                style.innerHTML = `
                    @layer theme, base, components, utilities;

                    @import "tailwindcss/theme.css" layer(theme) prefix(faf);
                    @import "tailwindcss/utilities.css" layer(utilities) prefix(faf);
                `;
                document.body.appendChild(style);

                const script = document.createElement( 'script' );
                script.setAttribute('src', 'https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4');
                document.body.appendChild(script);
            })();
        JS);
    }
}
