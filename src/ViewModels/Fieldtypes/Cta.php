<?php

namespace FewFar\Sitekit\ViewModels\Fieldtypes;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Statamic\Facades\Asset;
use Statamic\Facades\Entry;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\Statamic;
use Tiptap\Utils\HTML;

class Cta extends FieldType
{
    protected static $title = 'Call to Action';

    protected $categories = ['relationship'];
    protected $icon = 'link';

    public function augment($value)
    {
        $is_optional = ($this->config('enabled', 'always') === 'optional');
        $is_enabled = $is_optional ? (Arr::get($value, 'enabled') === true) : true;

        if (! $is_enabled) {
            return null;
        }

        $option = Arr::get($value, 'option');
        $is_entry = ($option === 'entry');
        $is_asset = ($option === 'asset');

        $entry = !$is_entry ? null : Entry::find(Arr::get($value, 'entry.0'));
        $asset = !$is_asset ? null : Asset::find(Arr::get($value, 'asset.0') ?? '');

        return [
            'option' => match (true) {
                $is_entry => 'entry',
                $is_asset => 'asset',
                default => 'url',
            },
            'label' => Arr::get($value, 'label') ?: Arr::get($value, 'label_default'),
            'url' => match (true) {
                $is_optional && !$is_enabled => null,
                $is_entry => $entry?->url(),
                $is_asset => $asset?->url(),
                default => Arr::get($value, 'url'),
            },
            'entry' => !$is_entry ? null : $entry,
            'asset' => !$is_asset ? null : $asset,
            'open_in_new_tab' => (Arr::get($value, 'open_in_new_tab') === true),
        ];
    }

    protected function configFieldItems(): array
    {
        return [
            'collections' => [
                'display' => __('Collections'),
                'instructions' => __('statamic::fieldtypes.link.config.collections'),
                'type' => 'collections',
                'mode' => 'select',
                'width' => 50,
            ],
            'enabled' => [
                'display' => __('Mode'),
                'instructions' => __('Changed UI to allow a CTA to be optional.'),
                'type' => 'select',
                'default' => 'always',
                'options' => [
                    'optional' => __('Optional'),
                    'always' => __('Always'),
                ],
                'width' => 50,
            ],
            'label' => [
                'display' => __('Label'),
                'instructions' => __('Show or hide the CTA text label.'),
                'type' => 'select',
                'default' => 'show',
                'options' => [
                    'show' => __('Show'),
                    'hide' => __('Hide'),
                ],
                'width' => 50,
            ],
            'label_placeholder' => [
                'display' => __('Label placeholder'),
                'instructions' => __('Show in the label field when empty.'),
                'type' => 'text',
                'width' => 50,
                'if' => [
                    'label' => 'equals show',
                ],
            ],
            'label_default' => [
                'display' => __('Label default'),
                'instructions' => __('Value for the label when field is empty.'),
                'type' => 'text',
                'width' => 50,
                'if' => [
                    'label' => 'equals show',
                ],
            ],
            'container' => [
                'display' => __('Container'),
                'instructions' => __('statamic::fieldtypes.link.config.container'),
                'type' => 'asset_container',
                'mode' => 'select',
                'max_items' => 1,
            ],
        ];
    }

    /**
     * The blank/default value.
     *
     * @return array
     */
    public function defaultValue()
    {
        return [
            'enabled' => $this->config('enabled', 'always') === 'always',
            'option' => 'url',
            'url' => null,
        ];
    }

    /**
     * Pre-process the data before it gets sent to the publish page.
     *
     * @param mixed $data
     * @return array|mixed
     */
    public function preProcess($data)
    {
        if ($this->config('enabled', 'always') === 'always') {
            $data['enabled'] = true;
        }

        else if (fluent($data ?? [])->missing('enabled')) {
            $data['enabled'] = false;
        }

        return $data;
    }

    /**
     * Process the data before it gets saved.
     *
     * @param mixed $data
     * @return array|mixed
     */
    public function process($data)
    {
        return $data;
    }

    public function preload()
    {
        $value = $this->field->value();

        $entryFieldtype = $this->makeNestedEntriesFieldtype(Arr::get($value, 'entry'));
        $assetFieldtype = $this->makeNestedAssetsFieldtype(Arr::get($value, 'asset'));

        return [
            'addLabel' => $this->config('add_label') ?: 'Add CTA',
            'enabled' => $this->config('enabled', 'always') === 'always' ? 'always' : 'optional',
            'label' => $this->config('label') === 'hide' ? 'hide' : 'show',
            'label_placeholder' => $this->config('label_placeholder'),
            'entry' => [
                'config' => $entryFieldtype->config(),
                'meta' => $entryFieldtype->preload(),
            ],
            'asset' => [
                'config' => $assetFieldtype->config(),
                'meta' => $assetFieldtype->preload(),
            ],
        ];
    }

    protected function makeNestedEntriesFieldtype($value): Fieldtype
    {
        $field = (new Field('entry', [
            'type' => 'entries',
            'max_items' => 1,
            'create' => false,
        ]));

        $field->setValue($value);

        $field->setConfig(array_merge(
            $field->config(),
            ['collections' => $this->config('collections')]
        ));

        return $field->fieldtype();
    }

    protected function makeNestedAssetsFieldtype($value): Fieldtype
    {
        $field = (new Field('asset', [
            'type' => 'assets',
            'max_files' => 1,
            'allow_uploads' => false,
            'mode' => 'list',
        ]));

        $field->setValue($value);

        $field->setConfig(array_merge(
            $field->config(),
            ['container' => $this->config('asset_container') ?: 'assets']
        ));

        return $field->fieldtype();
    }

    public static function register()
    {
        parent::register();

        $js = File::get(__DIR__.'/CtaFieldtype.js');

        Statamic::inlineScript(strtr('<script>:js</script>', [ ':js' => $js ]));
    }
}
