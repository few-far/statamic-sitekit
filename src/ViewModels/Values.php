<?php

namespace FewFar\Sitekit\ViewModels;

use Illuminate\Support\HtmlString;
use Statamic\Assets\Asset;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Contracts\Taxonomies\Term as TermContract;
use Statamic\Entries\Entry;
use Statamic\Fields\LabeledValue;
use Statamic\Fields\Values as BaseValues;
use Statamic\Taxonomies\Term;

class Values extends BaseValues
{
    public function __construct(Entry|EntryContract|Term|TermContract|BaseValues|iterable|null $values = null)
    {
        $this->setInstance($values);
    }

    public function setInstance(Entry|EntryContract|Term|TermContract|BaseValues|iterable|null $values = null) : self
    {
        $this->instance = match (true) {
            $values instanceof BaseValues => $values->getProxiedInstance(),
            is_object($values) && method_exists($values, 'toAugmentedCollection') => $values->toAugmentedCollection(),
            default => collect($values),
        };

        return $this;
    }

    /**
     * Core
     */

    public function get(string $key)
    {
        return $this[$key];
    }

    public function values(string $key)
    {
        return values($this[$key]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, static>|\Illuminate\Support\Collection<int, mixed>
     */
    public function collect(string $key)
    {
        return collect($this[$key])->map(fn ($value) => match (true) {
            $value instanceof BaseValues => values($value),
            default => $value,
        });
    }

    public function collectIf(string $key)
    {
        $collection = $this->collect($key);

        return $collection->isEmpty() ? null : $collection;
    }

    /**
     * UI layer
     */

    public function html(string $key)
    {
        return when($this[$key], fn ($html) => new HtmlString($html));
    }

    public function field(string $key)
    {
        return $this->fieldtype($key)?->field();
    }

    public function fieldtype(string $key)
    {
        $value = $this->instance->get($key);

        if (! $value instanceof \Statamic\Fields\Value) {
            return null;
        }

        return $value->fieldtype();
    }

    /**
     * Intended for single option fields, e.g.: select, button_group, radio etc.
     */
    public function option(string $key)
    {
        $value = $this->get($key);

        if ($value instanceof LabeledValue) {
            return $value->value();
        }

        return $value;
    }

    /**
     * Intended for checkbox fields.
     *
     * @return \FewFar\Sitekit\ViewModels\Values<string, bool|mixed>
     */
    public function options(string $key)
    {
        if (! $field = $this->field($key)) {
            return values($this->get($key));
        }

        $values = $this->collect($key)->keyBy('key');

        return values(
            collect($field->config()['options'] ?? [])
                ->keyBy('key')
                ->map(fn ($_, $key) => $values->has($key))
        );
    }

    // public function cta(string $key)
    // {
    //     $value = $this->get($key);

    //     if (! ($value['url'] ?? null)) {
    //         return null;
    //     }

    //     return [
    //         'link' => attrs([
    //             'href' => $value['url'],
    //             'target' => $value['open_in_new_tab'],
    //             'download' => $value['asset']?->basename(),
    //         ]),
    //         'copy' => $value['label'] ?: $value['entry']?->get('title'),
    //         'type' => strval($this->get($key . '_type')) ?: null,
    //     ];
    // }

    /**
     * Intended for use with Cta fields
     */
    public function cta(string $key)
    {
        return app(CtaModel::class)
            ->setValues($this)
            ->setKey($key)
            ->model();
    }

    public function mapAsset(?Asset $asset)
    {
        return app(AssetModel::class)
            ->setAsset($asset)
            ->model();
    }

    public function asset(string $key)
    {
        return $this->mapAsset($this->get($key));
    }

    public function mapForm(?Entry $form)
    {
        if (! $form) {
            return null;
        }

        $values = values($form);

        return values([
            'type' => 'form',
            'id' => $form->id(),
            'submit_label' => $values->get('submit_label') ?: 'Submit',
            'submitted_message' => $values->get('submitted_message') ?: 'Thank you for submitting the form.',
            'fields' => $values->collect('fields'),
        ]);
    }
}
