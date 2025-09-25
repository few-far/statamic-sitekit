<?php

namespace FewFar\Sitekit\Database\Seeders;

use Illuminate\Database\Seeder;
use Statamic\Eloquent\Entries\Entry;

class EntrySeeder extends Seeder
{
    public ?string $blueprint = null;

    /**
     * Optional. Collection where entry is mounted.
     */
    public ?string $mount = null;

    public Entry $entry;

    public function data()
    {
        return [];
    }

    public function setEntry($entry)
    {
        $this->entry = $entry;

        return $this;
    }
}
