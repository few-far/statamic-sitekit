<?php

namespace FewFar\Sitekit\SocialShare;

use FewFar\Sitekit\ViewModels\Listeners\CreatePageModel;
use Illuminate\Http\Request;
use Statamic\Facades\Entry;

class HtmlController
{
    /**
     * Render the Entry's social share template.
     */
    public function __invoke(Request $request, EntryFinder $entries)
    {
        $entry = $entries->find($request->route('id')) ?? abort(404);;

        return app(CreatePageModel::class)
            ->mapper($entry)
            ->socialShareView() ?? abort(404);
    }
}
