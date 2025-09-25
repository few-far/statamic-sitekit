<?php

namespace FewFar\Sitekit\Database\Seeders;

use Illuminate\Database\Seeder;
use Statamic\Facades;
use Statamic\Globals\GlobalSet;

class GlobalSetSeeder extends Seeder
{
    protected ?string $handle = null;
    protected ?string $title = null;
    protected GlobalSet $set;

    public function run()
    {
        $this->set = $this->createGlobalSet();

        $this->set->addLocalization(
            tap($this->set->makeLocalization(Facades\Site::default()->handle()))
                ->data($this->fixture())
        );

        $this->set->save();
    }

    protected function createGlobalSet()
    {
        $handle = $this->handle ?? (
            str(class_basename($this))
                ->before('Seeder')
                ->snake()
                ->value()
        );

        $title = $this->title ?? (
            str(class_basename($this))
                ->before('Seeder')
                ->headline()
                ->value()
        );

        return tap(Facades\GlobalSet::make($handle)->title($title))->save();
    }

    protected function fixture()
    {
        return [];
    }
}
