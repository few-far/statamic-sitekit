<?php

namespace FewFar\Sitekit\Redirects;

use FewFar\Sitekit\ViewModels\Listeners\CreatePageModel;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;
use Statamic\Contracts\Entries\Entry;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\GlobalSet;
use Statamic\Statamic;

class HandleNotFoundException
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

    /**
     * Search stored redirects for matching case.
     *
     * @param callable(NotFoundHttpException $ex): mixed $next
     */
    protected function attemptCmsableRedirect(NotFoundHttpException $ex, Closure $next): mixed
    {
        $path = ('/' . trim($this->request->getPathInfo(), '/'));
        $redirect = $this->findRedirect($path);

        $this->log($path, $redirect);

        if ($redirect) {
            return response()->redirectTo($redirect->target, $redirect->code);
        }

        return $next($ex);
    }

    /**
     * Create an instance of the logger.
     */
    protected function logger(): NotFoundLogger
    {
        return app(NotFoundLogger::class);
    }

    /**
     * Record the given request.
     */
    protected function log(string $path, ?Redirect $redirect): void
    {
        $this
            ->logger()
            ->setRequest($this->request)
            ->setPath($path)
            ->setRedirect($redirect)
            ->log();
    }

    /**
     * Find a Redirect, if any, for the given path.
     */
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

    /**
     * Check the global site settings for entry selected as 404 page.
     *
     * @param callable(NotFoundHttpException $ex): mixed $next
     * @throws Exception  when CMS not configured with an entry.
     */
    protected function attemptNotFoundEntry(NotFoundHttpException $ex, Closure $next): mixed
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

        return $next($ex);
    }
}
