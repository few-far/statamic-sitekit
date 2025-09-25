<?php

namespace FewFar\Sitekit\ViewModels;

use FewFar\Sitekit\ViewModels\PageModel;
use FewFar\Sitekit\ViewModels\Values;
use Illuminate\Support\Uri;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades;
use Statamic\Structures\Page;

abstract class Mapper
{
    public Entry $entry;
    public PageModel $model;

    public function setEntry(Entry $entry)
    {
        $this->entry = $entry;

        return $this;
    }

    public function createModel()
    {
        return app(PageModel::class)
            ->setEntry($this->entry)
            ->setMapper($this);
    }

    public function model()
    {
        if (! isset($this->model)) {
            $this->model = $this->createModel();
            $this->build();
        }

        return $this->model;
    }

    public function build()
    {
        $this->model ??= $this->model();

        $this->model->settings = $this->settings();

        $this->model->meta = $this->meta();
        $this->model->header = $this->header();
        $this->model->footer = $this->footer();
        $this->model->breadcrumbs = $this->breadcrumbs();
        $this->model->navs = $this->navs();
        $this->model->blocks = $this->blocks();

        // Give each block it's an incrementing id for page rendering
        $this->model->blocks->each(function ($block, $index) {
            $block->index ??= $index;
            $block->buildModel($this->model);
        });

        return $this;
    }

    public function header()
    {
        return values();
    }

    public function footer()
    {
        return values();
    }

    protected $showBreadcrumb = true;

    /**
     * @return \Illuminate\Support\Collection<\FewFar\Sitekit\ViewModels\Values>
     */
    public function breadcrumbs()
    {
        if (! $this->showBreadcrumb || ! $this->model->entry) {
            return collect();
        }

        return app(Breadcrumbs::class)
            ->setEntry($this->model->entry)
            ->breadcrumbs();
    }

    public function settings()
    {
        $settings = Facades\GlobalSet::find('site_settings')->inCurrentSite();

        return values($settings->toAugmentedCollection());
    }

    protected function makeNavItem(Page $page)
    {
        $current = request()->uri()->path();

        $item = [
            'link' => attrs([
                'href' => $page->url(),
                'aria-current' => when(strval($page->url()), fn ($url) => (
                    strval($current === ('/' . Uri::of($url)->path()))
                        ? 'page'
                        : null
                )),
            ]),
            'copy' => $page->title(),
        ];

        if ($children = $page->pages()) {
            $item['children'] = $children->all()->map(function ($page) {
                return $this->makeNavItem($page);
            });
        }

        return $item;
    }

    public function navs()
    {
        $navs = Facades\Nav::all()
            ->keyBy->handle
            ->map(fn($nav) => $nav->in(Facades\Site::current()))
            ->map(fn($tree) => $tree->pages()->all())
            ->map->map(function ($page) {
                return $this->makeNavItem($page);
            });

        return values($navs);
    }

    public function makePageTitle()
    {
        if ($title = $this->model->values->get('page_meta_title')) {
            return $title;
        }

        return $this->model->values->get('title') . ' ' . $this->makePageTitleSuffix();
    }

    public function makePageTitleSuffix()
    {
        if ($this->model->values->get('page_meta_title_no_suffix')) {
            return null;
        }

        return $this->model->settings->get('site_meta_title_suffix');
    }

    public function meta()
    {
        return values([
            'page_title' => trim($this->makePageTitle()),
            'page_description' => $this->model->values->get('page_meta_description'),
            'social_title' => $this->model->values->get('page_social_title'),
            'social_description' => $this->model->values->get('page_social_description'),
            'social_image' => $this->model->values->get('page_social_image')?->url(),
        ]);
    }

    public function blocks()
    {
        if (! isset($this->model->values)) {
            return collect();
        }

        $blocks = [
            ...$this->before(),
            ...$this->toComponentsFromFields($this->model->values->collect('blocks')),
            ...$this->after(),
        ];

        return collect($blocks)
            ->filter->isVisible()
            ->values();
    }

    public function before()
    {
        return [];
    }

    public function after()
    {
        return [];
    }

    /**
     * @param \Illuminate\Support\Collection<int, \FewFar\Sitekit\ViewModels\Values>  $fields
     * @return \Illuminate\Support\Collection<int, \FewFar\Sitekit\ViewModels\BlockComponent>
     */
    public function toComponentsFromFields($fields)
    {
        return $fields->flatMap(function (Values $values) {
            $type = $values['type'];

            if ($type === 'reusable_content') {
                return $this->toComponentsFromReusableContent($values->get('entry'));
            }

            $basename = str($type)->studly() . 'Block';

            /** @var class-string<BlockComponent> */
            $class = 'App\\View\\Components\\Blocks\\' . $basename;

            return [
                app($class)->setBlock($values)
            ];
        });
    }

    public function toComponentsFromReusableContent($entry)
    {
        if (! $entry) {
            return [];
        }

        $fields = values($entry)->collect('blocks');

        return $this->toComponentsFromFields($fields);
    }
}
