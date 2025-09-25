<?php

namespace FewFar\Sitekit\Database\Seeders;

use Illuminate\Database\Seeder;
use Statamic\Facades;
use Statamic\Structures\Nav;

class NavigationSeeder extends Seeder
{
    protected ?string $handle = null;
    protected Nav $nav;

    public function run()
    {
        $this->nav = $this->createNav();

        $this->createTree($this->fixtures());
    }

    protected function configure(Nav $nav)
    {
    }

    protected function createNav()
    {
        $handle = $this->handle ?? (
            str(class_basename($this))
                ->before('Seeder')
                ->snake()
                ->value()
        );

        $nav = Facades\Nav::make($handle)->title(
            str(class_basename($this))
                ->before('Seeder')
                ->title()
                ->value()
        );

        return tap($this->configure($nav) ?? $nav)->save();
    }

    protected function createTree($fixtures)
    {
        /** @var \Statamic\Structures\NavTree */
        $tree = $this->nav->makeTree(Facades\Site::default());
        $tree->save();

        $tree->tree($fixtures);

        $tree->save();
    }

    protected function fixtures()
    {
        return [];
    }
}
