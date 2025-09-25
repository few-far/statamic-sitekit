<?php

namespace FewFar\Sitekit\Redirects;

use FewFar\Sitekit\ViewModels\Listeners\CreatePageModel;
use Closure;
use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\Support\Facades\Schema;
use Statamic\Contracts\Entries\Entry;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\GlobalSet;
use Symfony\Component\HttpFoundation\Response;

class HandlesNotFoundExceptions
{
    /**
     * Check the global site settings for entry selected as 404 page.
     *
     * @throws Exception  when CMS not configured with an entry.
     */
    public function handle(NotFoundHttpException $ex, Request $request) : Responsable|Response
    {
        return Pipeline::send($request)
            ->through([
                $this->attemptCmsableRedirect(...),
                $this->attemptNotFoundEntry(...),
            ])
            ->thenReturn();
    }

    protected function attemptCmsableRedirect(Request $request, Closure $next)
    {
        $should_attempt = Schema::hasTable('cms_redirects') && Schema::hasTable('cms_redirects_logs');

        if (! $should_attempt) {
            return $next($request);
        }

        $path = ('/' . trim($request->getPathInfo(), '/'));
        $redirect = $this->findRedirect($path);

        DB::table('cms_redirects_logs')->insert([
            'created_at' => DB::raw('NOW()'),
            'updated_at' => DB::raw('NOW()'),
            'url' => $request->url(),
            'path' => $path,
            'redirect' => $redirect?->target,
            'redirect_id' => $redirect?->id,
        ]);

        if ($redirect) {
            return response()->redirectTo($redirect->target, $redirect->code);
        }

        return $next($request);
    }

    protected function findRedirect(string $path)
    {
        return Redirect::query()
            ->where('enabled', true)
            ->where(function ($query) use ($path) {
                $query->orWhere(function ($query) use ($path) {
                    $query->where('source_type', 'equals');
                    $query->where('source', [ $path ]);
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

    protected function attemptNotFoundEntry(Request $request, Closure $next) : Responsable
    {
        $settings = values(GlobalSet::find('site_settings')->inCurrentSite()->toAugmentedCollection());

        /** @var ?Entry */
        $entry = $settings->get('not_found_entry');

        if (! $entry) {
            throw new Exception('No "not found entry" set in site settings');
        }

        app(CreatePageModel::class)->loadModelIntoContext($entry);

        return $entry;
    }
}
