<?php

namespace FewFar\Sitekit\Database\Seeders;

use Exception;
use Illuminate\Database\Seeder;
use Statamic\Eloquent\Entries\Entry;
use Statamic\Fieldtypes\Bard;

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

    function bard(?string $attribute)
    {
        $bard = $this->entry->blueprint()
            ->field($attribute)
            ->fieldtype();

        return new class($bard) {
            public function __construct(public Bard $bard)
            {
            }

            public function make(iterable $items)
            {
                return collect($items)
                    ->flatMap(fn ($item) => match (true) {
                        is_string($item) => $this->bard->preProcess($item),
                        is_array($item) => [
                            [
                                'type' => 'set',
                                'attrs' => [ 'values' => $item ],
                            ],
                        ],
                        default => throw new Exception('Unexpected type for $item, only strings (as html) and sets (blocks) are supported.')
                    })
                    ->all();
            }
        };
    }
}
