<?php

namespace FewFar\Sitekit\MonkeyPatch;

use Statamic\Statamic;
use Statamic\Facades\Entry;

class Entries extends \Statamic\Fieldtypes\Entries
{
    public function augment($values)
    {
        if (config('statamic.system.always_augment_to_query', false)) {
            return parent::augment($values);
        }

        $items = Entry::findByIds($values);

        if ($this->config('max_items') === 1) {
            return $items->first();
        }

        return $items
            ->where(fn ($entry) => $entry->status() === 'published')
            ->values();
    }
}
