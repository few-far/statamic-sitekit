<?php

namespace FewFar\Sitekit\Database\Seeders;

use Illuminate\Support\Collection;
use Statamic\Contracts\Entries\Entry;

class EntryFixture
{
    public array|string $original;
    public ?EntrySeeder $seeder;
    public Collection $children;
    public Entry $entry;

    public function setOriginal(array|string $original)
    {
        $this->original = $original;

        return $this;
    }

    public function setSeeder(?EntrySeeder $seeder)
    {
        $this->seeder = $seeder;

        return $this;
    }

    public function setChildren(Collection $children)
    {
        $this->children = $children;

        return $this;
    }

    public function setEntry(Entry $entry)
    {
        $this->entry = $entry;

        return $this;
    }
}
