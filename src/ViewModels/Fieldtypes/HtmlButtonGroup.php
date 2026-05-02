<?php

namespace FewFar\Sitekit\ViewModels\Fieldtypes;

use Statamic\Fieldtypes\ButtonGroup;
use Statamic\Statamic;
use Illuminate\Support\Facades\File;
use Composer\Semver\VersionParser;

class HtmlButtonGroup extends ButtonGroup
{
    protected $icon = 'button_group';

    public static function register()
    {
        parent::register();

        if (\Composer\InstalledVersions::satisfies(new VersionParser, 'statamic/cms', '6.*')) {
            Statamic::inlineScript(File::get(__DIR__.'/HtmlButtonGroupFieldtype-v6.js'));
        } else {
            Statamic::inlineScript(File::get(__DIR__.'/HtmlButtonGroupFieldtype.js'));
        }
    }
}
