<?php

namespace FewFar\Sitekit\ViewModels\Concerns;

trait MakesSocialShareView
{
    /**
     * Creates an instance of the Mapper's social share view, if any.
     */
    public function socialShareView()
    {
        if (! $view = $this->makeSocialShareViewName()) {
            return null;
        }

        return view($view, $this->makeSocialShareViewData());
    }

    /**
     * Controls which view should be used to render the Social Share image. Use `null` to
     * disable this functionality for this Mapper, or override `makeSocialShareViewName`
     * to control which template is used on a per entry basis.
     *
     * @var string|null
     */
    protected $socialShareViewName = 'social-share';

    /**
     * Name of the social share view.
     */
    public function makeSocialShareViewName()
    {
        return $this->socialShareViewName;
    }

    /**
     * Data for the social share view.
     */
    public function makeSocialShareViewData()
    {
        return [
            'model' => values($this->makeSocialShareViewModel()),
        ];
    }

    public function makeSocialShareViewModel()
    {
        return [
            'label' => $this->values->page_share_image_label ?: $this->values->page_label,
            'heading' => $this->values->page_share_image_heading ?: $this->values->page_share_title ?: $this->values->page_heading ?: $this->values->title,
            'description' => $this->values->page_share_image_description ?: $this->values->page_share_description ?: $this->values->html('page_excerpt'),
        ];
    }
}
