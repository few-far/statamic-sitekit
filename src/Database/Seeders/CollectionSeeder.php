<?php

namespace FewFar\Sitekit\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;
use Statamic\Entries\Collection;
use Statamic\Facades;
use Statamic\Structures\CollectionTree;

class CollectionSeeder extends Seeder
{
    /**
     * Optional. The collection's handle in Statamic.
     */
    protected ?string $handle = null;

    /**
     * The collection, assigned at runtime.
     */
    protected Collection $collection;

    /**
     * The collection's tree, assigned at runtime.
     */
    protected ?CollectionTree $tree = null;

    /**
     * Override to change where seeders are automatically located.
     */
    protected $seederNamespace = 'Database\Seeders\Content\Collections';

    /**
     * Run the seeder.
     */
    public function run()
    {
        $this->collection ??= $this->collection();

        if ($this->collection->structure()) {
            $this->tree = $this->collection
                ->structure()
                ->in(Facades\Site::default()->handle());
        }

        $items = $this->items();

        Event::listen(Events\TypesCreated::class, fn () => $this->seed($items));
    }

    /**
     * Created the entires and returnes the entry fixtures
     */
    protected function items()
    {
        return $this->createEntries($this->fixtures());
    }

    /**
     * Finds the collection by $handle, using convention to load otherwise.
     */
    protected function collection()
    {
        return Facades\Collection::find($this->handle ?? (
            str(class_basename($this))
                ->before('Seeder')
                ->snake()
                ->value()
        ));
    }

    /**
     * Returns the data for the Entry, removing fixture specific values.
     */
    protected function data($fixture)
    {
        if (is_string($fixture)) {
            return [ 'title' => $fixture ];
        }

        return collect($fixture)
            ->except('@seeder', '@mount', '@blueprint', '@children')
            ->all();
    }

    /**
     * Finds the seeder for the given fixture.
     */
    public function seeder($entry, $fixture)
    {
        if ($class = data_get($fixture, '@seeder')) {
            return app($class)->setEntry($entry);
        }

        $class = implode('\\', [
            $this->seederNamespace,
            str($this->collection->handle())->studly(),
            str($entry->slug())->studly() . 'Seeder',
        ]);

        if (class_exists($class)) {
            return app($class)->setEntry($entry);
        }

        return null;
    }

    /**
     * Create the entries for the given fixtures. For collections with trees
     * this will be called with optional parents.
     */
    protected function createEntries($fixtures, $parent = null)
    {
        if (! is_iterable($fixtures)) {
            return collect();
        }

        return collect($fixtures)->map(function ($fixture, $slug) use ($parent) {
            $entry = Facades\Entry::make()
                ->slug($slug)
                ->collection($this->collection)
                ->data($this->data($fixture));

            $seeder = $this->seeder($entry, $fixture);

            $entry->blueprint($fixture['@blueprint'] ?? $seeder?->blueprint);
            $entry->save();

            if ($this->tree) {
                if ($parent) {
                    $this->tree->appendTo($parent->id(), $entry)->save();
                } else {
                    $this->tree->append($entry)->save();
                }
            }

            $entry->publish();

            if ($mount = $fixture['@mount'] ?? $seeder?->mount) {
                Facades\Collection::find($mount)
                    ->mount($entry->id())
                    ->save();
            }

            $children = $this->createEntries($fixture['@children'] ?? null, $entry);

            return app(EntryFixture::class)
                ->setOriginal($fixture)
                ->setSeeder($seeder)
                ->setChildren($children)
                ->setEntry($entry);
        });
    }

    /**
     * Assigns the values from the seeder to the given items
     *
     * @param \Illuminate\Support\Collection<EntryFixture> $items
     */
    public function seed($items)
    {
        /** @var EntryFixture */
        foreach ($items as $item) {
            if ($item->seeder) {
                foreach ($item->seeder->data() as $key => $value) {
                    $item->entry->set($key, $value);
                }

                $item->entry->save();
            }

            if ($item->children) {
                $this->seed($item->children);
            }
        }
    }

    /**
     * Defines an the entry fixutres.
     */
    protected function fixtures()
    {
        return [];
    }
}
