<?php

namespace FewFar\Sitekit\Forms\Fieldtypes;

use Statamic\Fields\Fieldtype;

class FormSubmissionsLink extends Fieldtype
{
    protected $categories = ['special'];
    protected $icon = 'link';

    public function preload()
    {
        return [
            'action' => cp_route('submissions.index', $this->field()->parent()->id()),
        ];
    }


    public function preProcessIndex($data)
    {
        return [
            'action' => cp_route('submissions.index', $this->field()->parent()->id()),
        ];
    }

    public static function register()
    {
        parent::register();

        $template = <<<'HTML'
        HTML;

        \Statamic\Statamic::inlineScript(<<<'JS'
            document.addEventListener('cp:init', () => {
                Statamic.$components.register('form_submissions_link-fieldtype', {
                    mixins: [ window.Fieldtype ],

                    template: `
                        <a :href="meta.action" class="text-sm text-blue">View submissions</a>
                    `

                });

                Statamic.$components.register('form_submissions_link-fieldtype-index', {
                    mixins: [ window.IndexFieldtype ],

                    template: `
                        <a :href="value.action" class="btn btn-xs">View submissions</a>
                    `,
                });
            });
        JS);
    }
}
