<?php

namespace FewFar\Sitekit\ViewModels;

use FewFar\Sitekit\ViewModels\Values;
use Illuminate\Support\Collection;
use Statamic\Contracts\Entries\Entry;

class PageModel
{
    public Mapper $mapper;
    public ?Entry $entry = null;
    public Values $values;
    public Values $meta;
    public Values $settings;
    public Values $header;
    public Values $footer;
    public Values $navs;
    public ?Collection $breadcrumbs;
    public Collection $blocks;

    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;

        return $this;
    }

    public function setEntry(Entry $entry)
    {
        $this->entry = $entry;
        $this->values = values($entry);

        return $this;
    }
}
