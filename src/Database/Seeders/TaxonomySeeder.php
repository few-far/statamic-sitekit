<?php

namespace FewFar\Sitekit\Database\Seeders;

use Illuminate\Database\Seeder;
use Statamic\Facades;
use Statamic\Taxonomies\Taxonomy;

class TaxonomySeeder extends Seeder
{
    protected ?string $handle = null;
    protected Taxonomy $taxonomy;

    public function run()
    {
        $this->taxonomy = $this->createTaxonomy();

        $this->createTerms($this->fixtures());
    }

    protected function configure(Taxonomy $taxonomy)
    {
    }

    protected function createTaxonomy()
    {
        $handle = $this->handle ?? (
            str(class_basename($this))
                ->before('Seeder')
                ->snake()
                ->value()
        );

        $taxonomy = Facades\Taxonomy::make($handle)->title(
            str(class_basename($this))
                ->before('Seeder')
                ->title()
                ->value()
        );

        return tap($this->configure($taxonomy) ?? $taxonomy)->save();
    }

    protected function createTerms($fixtures)
    {
        return collect($fixtures)->map(function ($fixture, $handle) {
            return Facades\Term::make($handle)
                ->taxonomy($this->taxonomy)
                ->dataForLocale(Facades\Site::default()->handle(), $this->data($fixture))
                ->save();
        });
    }

    protected function data($fixture)
    {
        if (is_string($fixture)) {
            return [ 'title' => $fixture ];
        }

        return $fixture;
    }

    protected function fixtures()
    {
        return [];
    }
}
