<?php

namespace FewFar\Sitekit\MonkeyPatch;

use Statamic\Eloquent\Sites\Sites as BaseSites;

class Sites extends BaseSites
{
    protected function getSavedSites()
    {
        if (config('domain.limbo')) {
            return $this->getFallbackConfig();
        }

        return parent::getSavedSites();
    }
}
