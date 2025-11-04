<?php

namespace FewFar\Sitekit\ViewModels;

use FewFar\Sitekit\ViewModels\Values;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Component;

abstract class BlockComponent extends Component implements Htmlable
{
    /**
     * Create an instance of a BlockComponent, intended to be rendered directly within
     * a blade template.
     */
    public function __construct(
        protected Values|null $block = null,
        public Values|iterable|null $model = null,
        public ?int $index = null,
    )
    {
        if ($model) {
            $this->model = values($model);
        }
    }

    public function setBlock(Values|iterable $block)
    {
        $this->block = values($block);

        return $this;
    }

    /**
     * Get the methods that should be ignored.
     *
     * @return array
     */
    protected function ignoredMethods()
    {
        return array_merge(parent::ignoredMethods(), [
            'buildModel',
            'isVisible',
            'setBlock',
            'toHtml',
            'toModel',
        ]);
    }

    /**
     * Called by the mapper to give each block an opportunity to create it's viewmodel.
     *
     * @return self
     */
    public function buildModel(PageModel $page)
    {
        $this->model = values($this->toModel($this->block, $page));

        return $this;
    }

    /**
     * Implemented by subclasses to transform the Statamic replicator block into
     * a bag of values for frontend rendering.
     *
     * @return Values
     */
    public function toModel(Values $block, PageModel $page)
    {
        return values();
    }

    /**
     * Render the component directly.
     *
     * @return string
     */
    public function toHtml()
    {
        return Blade::renderComponent($this);
    }

    public function isVisible()
    {
        return true;
    }

    /**
     * Variable provided to block templates to allow for a fallback name for accessibility.
     *
     * @return string
     */
    public function ariaLabel()
    {
        return str($this::class)
            ->classBasename()
            ->beforeLast('Block')
            ->headline()
            ->value();
    }
}
