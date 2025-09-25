<?php

namespace FewFar\Sitekit\ViewModels;

use Statamic\Contracts\Entries\Entry;
use Statamic\Structures\Page;

class Breadcrumbs
{
    protected Entry|Page $entry;

    public function setEntry(Entry|Page $entry)
    {
        $this->entry = $entry;

        return $this;
    }

    public function breadcrumbs()
    {
        $current = $this->entry->uri();

        return $this->ancestorsAndSelf()->map(fn ($entry) => values([
            'link' => attrs([
                'href' => $entry->uri(),
                'aria-current' => $entry->uri() === $current ? 'page' : null,
            ]),
            'copy' => $entry->get('title'),
        ]));
    }

    public function ancestorsAndSelf()
    {
        $entry = ($this->entry instanceof Page)
            ? $this->entry->entry()
            : $this->entry;

        $breadcrumbs = collect();

        for ($i = 0; $i < 10; $i += 1) {
            $breadcrumbs->push($entry);

            if ($parent = $entry->parent()) {
                $entry = ($parent instanceof Page)
                    ? $parent->entry()
                    : $parent;
                continue;
            }

            if ($mount = $entry->collection()->mount()) {
                $entry = $mount->page()->entry();
                continue;
            }

            break;
        }

        return $breadcrumbs
            ->reverse()
            ->values();
    }
}
