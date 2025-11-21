<?php

namespace FewFar\Sitekit\ViewModels;

use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Tappable;
use Illuminate\View\ComponentAttributeBag;

class CtaModel
{
    use Tappable;
    use Conditionable;

    /**
     * Optional. Typically the values of the given block. Used to introspect the "{$key}_type" property.
     */
    protected ?Values $values = null;

    /**
     * Set values for the given block.
     */
    public function setValues(?Values $values) : static
    {
        $this->values = $values;

        return $this;
    }

    /**
     * Values for the given block.
     */
    public function values() : ?Values
    {
        return $this->values;
    }

    /**
     * Augmented value of the CTA field.
     *
     * @see \FewFar\Sitekit\ViewModels\Fieldtypes\Cta
     *
     * @var array|null
     */
    protected $value;

    /**
     * Set the augmented value of the CTA field.
     */
    public function setValue(?array $value) : self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Augmented value of the CTA field.
     */
    public function value() : ?array
    {
        return $this->value;
    }

    /**
     * Key used to access the augented value from the given Values.
     */
    protected ?string $key = null;

    /**
     * Set value access key.
     */
    public function setKey(string $key)
    {
        $this->key = $key;
        $this->value = $this->values?->get($key);

        return $this;
    }

    /**
     * Value access key.
     */
    public function key() : ?string
    {
        return $this->key;
    }

    /**
     * Changes the current augment value to use given Entry.
     *
     * @param \Statamic\Contracts\Entries\Entry|null
     */
    public function setEntry($entry) : self
    {
        $this->value ??= [];
        $this->value['option'] = 'entry';
        $this->value['entry'] = $entry;
        $this->value['url'] = $entry?->uri();

        return $this;
    }

    /**
     * Whether or not link opens in new tab.
     */
    public function setOpenInNewWindow(bool $bool) : self
    {
        $this->value ??= [];
        $this->value['open_in_new_window'] = $bool;

        return $this;
    }

    /**
     * Additional attributes to be merged into the link.
     *
     * @var array|null
     */
    protected $attrs;

    /**
     * Sets addition attributes.
     */
    public function setAttrs(?array $attrs) : self
    {
        $this->attrs = $attrs;

        return $this;
    }

    /**
     * Optional Copy for label of CTA.
     *
     * @var string|null
     */
    protected $copy;

    /**
     * Sets addition attributes.
     */
    public function setCopy(?string $copy) : self
    {
        $this->copy = $copy;

        return $this;
    }

    /**
     * Builds model for the current augmented CTA value.
     */
    public function model()
    {
        if (! data_get($this->value, 'url')) {
            return null;
        }

        return [
            'link' => $this->link(),
            'copy' => $this->copy(),
            'type' => $this->type(),
        ];
    }

    /**
     * Builds the link attribute bag.
     */
    public function link() : ComponentAttributeBag
    {
        return attrs(
            collect()
                ->merge([
                    'href' => $this->value['url'],
                    'target' => data_get($this->value, 'open_in_new_tab') ? '_blank' : null,
                    'download' => data_get($this->value, 'asset')?->basename(),
                ])
                ->merge($this->attrs ?? [])
                ->filter()
                ->all()
        );
    }

    /**
     * Builds up the label of the cta.
     */
    public function copy()
    {
        return (
            $this->copy
            ?: data_get($this->value, 'label')
            ?: $this->copyForEntry()
        );
    }

    /**
     * Builds up the label of the cta based on related entry.
     */
    public function copyForEntry()
    {
        $entry = data_get($this->value, 'entry');

        return (
            $entry->get('page_link_text')
            ?: $entry?->get('title')
        );
    }

    /**
     * @var ?string
     */
    protected $type;

    /**
     * Sets the type/variant of the CTA model. Overrides the option from values.
     */
    public function setType($type) : self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Builds the type by accessing a sibling key from values.
     */
    public function type() : ?string
    {
        return $this->type ?? strval($this->values?->get($this->key() . '_type')) ?: null;
    }
}
