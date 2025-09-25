<?php

namespace FewFar\Sitekit\ViewModels\Events;

use Illuminate\Contracts\View\View;
use Statamic\Contracts\Entries\Collection;
use Statamic\Fields\Blueprint;

class PreparingPublishForm
{
    public array $fields = [];

    public function __construct(
        public View $view,
        public Collection $collection,
        public Blueprint $blueprint,
    )
    {
    }
}
