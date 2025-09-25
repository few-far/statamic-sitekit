<?php

namespace FewFar\Sitekit\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Statamic\Exceptions\NotFoundHttpException;

class MigrationController
{
    public function view()
    {
        $result = Artisan::call('migrate --pretend');

        return View::file(__DIR__.'/resources/views/migrations.blade.php', [
            'url' => cp_route('utilities.migrations.run'),
            'result' => $result,
            'dryrun' => Artisan::output(),
        ]);
    }

    public function run()
    {
        $result = Artisan::call('migrate --force');

        return response()->json([
            'data' => [
                'result' => $result,
                'output' => Artisan::output(),
            ],
        ]);
    }
}
