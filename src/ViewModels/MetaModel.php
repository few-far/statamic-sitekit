<?php

namespace FewFar\Sitekit\ViewModels;

class MetaModel
{
    public function __construct(public PageModel $model)
    {
    }

    public function model()
    {
        return values([
            'page_title' => $this->pageTitle(),
            'page_description' => $this->pageDescription(),
            'page_cannonical' => $this->pageCannonical(),
            'social_title' => $this->socialTitle(),
            'social_description' => $this->socialDescription(),
            'social_image' => $this->socialImage(),
        ]);
    }

    public function pageTitle()
    {
        if ($title = $this->model->values->get('page_meta_title')) {
            return $title;
        }

        return $this->pageTitlePrefix() . ' ' . $this->pageTitleSuffix();
    }

    public function pageTitlePrefix()
    {
        return $this->model->values->get('title');
    }

    public function pageTitleSuffix()
    {
        if ($this->model->values->get('page_meta_title_no_suffix')) {
            return null;
        }

        return $this->model->settings->get('site_meta_title_suffix');
    }

    public function pageDescription()
    {
        return $this->model->values->get('page_meta_description');
    }

    public function pageCannonical()
    {
        if (! $uri = $this->model->entry?->uri()) {
            return null;
        }

        return url($uri);
    }

    public function socialTitle()
    {
        return $this->model->values->get('page_social_title');
    }

    public function socialDescription()
    {
        return $this->model->values->get('page_social_description');
    }

    public function socialImage()
    {
        return $this->model->values->get('page_social_image')?->url();
    }
}
