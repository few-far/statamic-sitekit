<?php

namespace FewFar\Sitekit\Forms\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Illuminate\Support\Facades\File;
use Statamic\Statamic;

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

        Statamic::inlineScript(File::get(__DIR__.'/FormSubmissionLink.js'));
        Statamic::inlineScript(File::get(__DIR__.'/FormSubmissionLinkIndex.js'));
    }
}
