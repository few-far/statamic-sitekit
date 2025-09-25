<?php

namespace FewFar\Sitekit\ViewModels\Listeners;

use Illuminate\Routing\Events\RouteMatched;

class DumpMatchedRoute
{
    public function handle(RouteMatched $route)
    {
        if (request()->query('debug') !== 'route') {
            return;
        }

        dd($route);
    }
}
