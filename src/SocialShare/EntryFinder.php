<?php

namespace FewFar\Sitekit\SocialShare;

use Statamic\Facades;

class EntryFinder
{
    /**
     * Locate the entry by ID
     *
     * @param string|int|null  $id
     * @return \Statamic\Contracts\Entries\Entry
     */
    public function find($id)
    {
        $query = $this->query();

        foreach ($this->mixins() as $mixin) {
            $query->tap($mixin);
        }

        return $query->find($id);
    }

    /**
     * Create an instance of the query builder.
     */
    public function query()
    {
        return Facades\Entry::query();
    }

    /**
     * Collect all the Mixins or Scopes for entries.
     */
    public function mixins()
    {
        return collect(config('sitekit.social_share.entry_mixins'))->map(function ($mixin) {
            if (is_string($mixin) && class_exists($mixin)) {
                return app($mixin);
            }

            if (is_string($mixin)) {
                return \Statamic\Facades\Scope::find($mixin);
            }

            return $mixin;
        });
    }
}
