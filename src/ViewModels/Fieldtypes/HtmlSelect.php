<?php

namespace App\Fieldtypes;

use Statamic\Fieldtypes\Select;
use Statamic\Statamic;
use Illuminate\Support\Facades\File;
use Composer\Semver\VersionParser;

class HtmlSelect extends Select
{
    protected $icon = 'select';

    public static function register()
    {
        parent::register();

        if (\Composer\InstalledVersions::satisfies(new VersionParser, 'statamic/cms', '6.*')) {
            Statamic::inlineScript(File::get(__DIR__.'/HtmlSelectFieldtype-v6.js'));
        }
    }
}
