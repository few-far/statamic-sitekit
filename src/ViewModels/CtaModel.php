<?php

namespace FewFar\Sitekit\ViewModels;

class CtaModel
{
    protected Values $values;

    public function setValues(Values $values)
    {
        $this->values = $values;

        return $this;
    }

    public function values()
    {
        return $this->values;
    }

    protected $value;

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function value()
    {
        return $this->value;
    }

    protected ?string $key = null;

    public function setKey(string $key)
    {
        $this->key = $key;
        $this->value = $this->values->get($key);

        return $this;
    }

    public function key()
    {
        return $this->key;
    }

    public function model()
    {
        if (! $this->value) {
            return null;
        }

        return [
            'link' => attrs([
                'href' => $this->value['url'],
                'target' => $this->value['open_in_new_tab'] ? '_blank' : null,
                'download' => $this->value['asset']?->basename(),
            ]),
            'copy' => $this->copy(),
            'type' => strval($this->values->get($this->key() . '_type')) ?: null,
        ];
    }

    public function copy()
    {
        return $this->value['label'] ?: $this->copyForEntry();
    }

    public function copyForEntry()
    {
        return (
            $this->value['entry']?->get('page_link_text')
            ?: $this->value['entry']?->get('title')
        );
    }
}
