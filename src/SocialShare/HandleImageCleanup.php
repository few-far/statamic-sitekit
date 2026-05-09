<?php

namespace FewFar\Sitekit\SocialShare;

use Statamic\Events\EntryDeleted;
use Statamic\Events\EntrySaved;

class HandleImageCleanup
{
    /**
     * Register events.
     */
    public function subscribe()
    {
        if (! config('sitekit.social_share.cache.enabled')) {
            return null;
        }

        return [
            EntrySaved::class => 'handleSaved',
            EntryDeleted::class => 'handleDeleted',
        ];
    }

    /**
     * Remove cached images if the Entry has changed.
     */
    public function handleSaved(EntrySaved $event, ImageGenerator $generator)
    {
        if ($generator->storage()->exists($generator->screenshotPath($event->entry))) {
            return;
        }

        $generator->cleanup($event->entry);
    }

    /**
     * Remove cached images when Entry is deleted.
     */
    public function handleDeleted(EntryDeleted $event, ImageGenerator $generator)
    {
        $generator->cleanup($event->entry);
    }
}
