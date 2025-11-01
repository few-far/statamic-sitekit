<?php

namespace FewFar\Sitekit\Redirects;

use FewFar\Sitekit\ViewModels\Listeners\CreatePageModel;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Lottery;
use Statamic\Contracts\Entries\Entry;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\GlobalSet;
use Statamic\Statamic;

class HandlesNotFoundExceptions
{
    /**
     * Creates an instance of the handler
     */
    public function __construct(protected Request $request)
    {
    }

    /**
     * Check the global site settings for entry selected as 404 page.
     *
     * @throws Exception  when CMS not configured with an entry.
     */
    public function handle(NotFoundHttpException $ex)
    {
        if (Statamic::isCpRoute()) {
            return null;
        }

        if (Statamic::isApiRoute()) {
            return null;
        }

        return Pipeline::send($ex)
            ->through([
                $this->attemptCmsableRedirect(...),
                $this->attemptNotFoundEntry(...),
            ])
            ->thenReturn();
    }

    protected function attemptCmsableRedirect(NotFoundHttpException $ex, Closure $next)
    {
        $path = ('/' . trim($this->request->getPathInfo(), '/'));
        $redirect = $this->findRedirect($path);

        DB::table('cms_redirect_logs')->insert([
            'created_at' => DB::raw('NOW()'),
            'updated_at' => DB::raw('NOW()'),
            'url' => $this->request->getRequestUri(),
            'path' => $path,
            'redirect' => $redirect?->target,
            'redirect_id' => $redirect?->id,
        ]);

        Lottery::odds(1, 100)
            ->winner(function () {
                DB::table('cms_redirect_logs')
                    ->where('created_at', '<', DB::raw('NOW() - interval \'1 year\''))
                    ->delete();
            })
            ->choose();

        if ($redirect) {
            return response()->redirectTo($redirect->target, $redirect->code);
        }



        return $next($ex);
    }

    protected function findRedirect(string $path)
    {
        return Redirect::query()
            ->where('enabled', true)
            ->where(function ($query) use ($path) {
                $query->orWhere(function ($query) use ($path) {
                    $query->where('source_type', 'equals');
                    $query->whereRaw('LOWER(source) = LOWER(?)', [ $path ]);
                });

                $query->orWhere(function ($query) use ($path) {
                    $query->where('source_type', 'like');
                    $query->whereRaw('? ILIKE source', [ $path ]);
                });

                $query->orWhere(function ($query) use ($path) {
                    $query->where('source_type', 'regex');
                    $query->whereRaw('? ~* source', [ $path ]);
                });
            })
            ->first();
    }

    protected function attemptNotFoundEntry(NotFoundHttpException $ex, Closure $next)
    {
        $settings = values(GlobalSet::find('site_settings')->inCurrentSite()->toAugmentedCollection());

        /** @var ?Entry */
        $entry = $settings->get('not_found_entry');

        if (! $entry) {
            throw new Exception('No "not found entry" set in site settings');
        }

        app(CreatePageModel::class)
            ->ignoreRouteMappers(true)
            ->loadModelIntoContext($entry);

        return null;
    }
}
