<?php

namespace FewFar\Sitekit\SocialShare;

use Illuminate\Http\Request;
use Statamic\Contracts\Entries\Entry;

class ImageController
{
    /**
     * Creates an instance of the controller.
     */
    public function __construct(
        protected ImageGenerator $images
    )
    {
    }

    /**
     * Return the social share image, either using the cache or generating a new.
     */
    public function __invoke(Request $request, EntryFinder $entries)
    {
        $entry = $entries->find($request->route('id')) ?? abort(404);;

        $this->handleHashRedirect($entry, $request->query('hash'));

        return response()->make($this->images->image($entry), 200, [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    /**
     * Checks to see if the has is the latest, redirecting if not.
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException if hash is out of date.
     */
    protected function handleHashRedirect(Entry $entry, ?string $hash)
    {
        if (! $hash) {
            return;
        }

        $latest = $this->images->screenshotHash($entry);

        if ($hash === $latest) {
            return;
        }

        $url = url()->signedRoute('sitekit.social-share', [
            'id' => $entry->id(),
            'hash' => $latest,
        ]);

        abort(response()->redirectTo($url));
    }
}
