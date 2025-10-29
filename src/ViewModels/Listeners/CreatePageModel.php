<?php

namespace FewFar\Sitekit\ViewModels\Listeners;

use FewFar\Sitekit\ViewModels\PageModel;
use Carbon\Carbon;
use Exception;
use Illuminate\Routing\Events\PreparingResponse;
use Statamic\Contracts\Entries\Entry;
use Statamic\Structures\Page;

class CreatePageModel
{
    protected bool $ignoreRouteMappers = false;

    public function ignoreRouteMappers(bool $ignore)
    {
        $this->ignoreRouteMappers = true;

        return $this;
    }

    /**
     * When the Statamic route finds a resource (entry, term, etc) we attempt
     * to build the page model, if appliable, and load it into the container.
     * PreparingReponse is fired once the route's action is called, after all
     * middleware has ran on the way in, but before the middleware is ran on
     * the way out.
     *
     * @return void
     */
    public function handle(PreparingResponse $event)
    {
        if (! $entry = $this->toEntry($event->response)) {
            return;
        }

        $this->handleExpiredRedirect($entry);

        $this->loadModelIntoContext($entry);
    }

    /**
     * Depending on the collection type, we have have been given a page in a
     * structure, so we try and coalese into an Entry.
     */
    public function toEntry($response) : ?Entry
    {
        return match (true) {
            $response instanceof Page => $response->entry(),
            $response instanceof Entry => $response,
            default => null,
        };
    }

    /**
     * Certain Entries can have explicit expiry dates and accompanying redirects.
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function handleExpiredRedirect(Entry $entry)
    {
        $values = values($entry);
        $expiry = $values->expiry_date;
        $redirect = $values->expiry_redirect;

        if (! ($redirect && $expiry instanceof Carbon)) {
            return;
        }

        if ($expiry->isFuture()) {
            return;
        }

        abort(redirect($redirect));
    }

    /**
     * Run the pipeline stack by finding the appropriate mapper and building
     * the PageModel, loading it into the container.
     */
    public function loadModelIntoContext(Entry $entry)
    {
        $model = $this->mapper($entry)->model();

        $this->checkDebug($model);

        app()->instance('model', $model);
    }

    /**
     * Quick access to PageModel in debug enviroments, terminating request.
     */
    public function checkDebug(PageModel $model)
    {
        if (! app('config')->get('app.debug')) {
            return;
        }

        if (request()->query('debug') !== 'model') {
            return;
        }

        dd($model);
    }

    /**
     * Resolve mappers from the app's CMS Service Provider.
     *
     * @return array<string, array<string, class-string<\FewFar\Sitekit\ViewModels\Mapper>>>
     */
    public function mappers()
    {
        return config('domain.mappers');
    }

    /**
     * Resolve the mapper for the given Entry.
     *
     * @throws \Exception  when CmsServiceProvider::$mappers['default'] not set
     * @return \FewFar\Sitekit\ViewModels\Mapper
     */
    public function mapper(Entry $entry)
    {
        return app($this->mapperClass($entry))
            ->setEntry($entry);
    }

    protected function mapperClass(Entry $entry)
    {
        $collection = $entry->collectionHandle();
        $blueprint = $entry->blueprint()->handle();

        $mappers = $this->mappers();

        if (! $this->ignoreRouteMappers) {
            if ($route = $mappers['@routes'][request()->route()->getName()] ?? null) {
                return $route;
            }
        }

        if ($type = $mappers[$collection][$blueprint] ?? null) {
            return $type;
        }

        if (class_exists($guess = 'App\\Mappers\\' . str($collection)->studly() . '\\' . str($blueprint)->studly() . 'Mapper')) {
            return $guess;
        }

        if ($default = $mappers['default'] ?? null) {
            return $default;
        }

        if (class_exists($fallback = \App\Mappers\Mapper::class)) {
            return $fallback;
        }

        throw new Exception('No default mapper set in domain.mappers config');
    }
}
