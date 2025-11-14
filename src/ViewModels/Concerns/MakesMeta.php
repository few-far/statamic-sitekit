<?php

namespace FewFar\Sitekit\ViewModels\Concerns;

trait MakesMeta
{
    public function meta()
    {
        return values([
            'page_title' => $this->makeMetaPageTitle(),
            'page_description' => $this->makeMetaPageDescription(),
            'page_cannonical' => $this->makeMetaPageCannonical(),
            'social_title' => $this->makeMetaSocialTitle(),
            'social_description' => $this->makeMetaSocialDescription(),
            'social_image' => $this->makeMetaSocialImage(),
        ]);
    }

    public function makeMetaPageTitle()
    {
        if ($title = $this->model->values->get('page_meta_title')) {
            return $title;
        }

        return $this->makeMetaPageTitlePrefix() . ' ' . $this->makeMetaPageTitleSuffix();
    }

    public function makeMetaPageTitlePrefix()
    {
        return $this->model->values->get('title');
    }

    public function makeMetaPageTitleSuffix()
    {
        if ($this->model->values->get('page_meta_title_no_suffix')) {
            return null;
        }

        return $this->model->settings->get('site_meta_title_suffix');
    }

    public function makeMetaPageDescription()
    {
        return $this->model->values->get('page_meta_description');
    }

    public function makeMetaPageCannonical()
    {
        if (! $uri = $this->model->entry?->uri()) {
            return null;
        }

        return url($uri);
    }

    public function makeMetaSocialTitle()
    {
        return $this->model->values->get('page_social_title');
    }

    public function makeMetaSocialDescription()
    {
        return $this->model->values->get('page_social_description');
    }

    public function makeMetaSocialImage()
    {
        return $this->model->values->get('page_social_image')?->url();
    }
}
