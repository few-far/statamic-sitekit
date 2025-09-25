<?php

namespace FewFar\Sitekit\Database\Seeders;

use FewFar\Sitekit\Forms\Submission;
use FewFar\Sitekit\Database\Seeders\Events\TypesCreated;
use Illuminate\Database\Seeder;
use Statamic\Eloquent\Revisions\RevisionModel;
use Statamic\Facades;

abstract class StatamicSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->makeCoreTypes();
        $this->makeRecreateableTypes();
    }

    public function makeCoreTypes()
    {
        $this->createCmsUsers();
    }

    protected function resetRecreateableTypes()
    {
        RevisionModel::truncate();
        Submission::truncate();
        Facades\Entry::query()->truncate();

        Facades\Collection::all()
            ->filter->hasStructure()
            ->map->structure()
            ->map->in(Facades\Site::default()->handle())
            ->each->tree([])
            ->each->save();

        Facades\Nav::all()->each->delete();
        Facades\Collection::all()->each->delete();
        Facades\GlobalSet::all()->each->delete();
        Facades\Taxonomy::all()->each->delete();
        Facades\Term::query()->truncate();
        Facades\Asset::query()->truncate();
        Facades\AssetContainer::all()->each->delete();
    }

    public function makeRecreateableTypes()
    {
        $this->createAssets();

        $this->createCollections();
        $this->createForms();
        $this->createNavigations();
        $this->createTaxonomies();
        $this->createGlobalSets();

        TypesCreated::dispatch();
    }

    abstract protected function createCmsUsers();

    abstract protected function createAssets();

    abstract protected function createCollections();

    abstract protected function createNavigations();

    abstract protected function createGlobalSets();

    abstract protected function createTaxonomies();

    abstract protected function createForms();
}
