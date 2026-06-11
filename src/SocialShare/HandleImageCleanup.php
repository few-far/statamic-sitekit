<?php

namespace FewFar\Sitekit\SocialShare;

use Statamic\Events\EntryDeleted;
use Statamic\Events\EntrySaved;

class HandleImageCleanup
{
    /**
     * Creates an instance of the Subscriber.
     */
    public function __construct(protected ImageGenerator $generator)
    {
    }

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
    public function handleSaved(EntrySaved $event)
    {
        if ($this->generator->storage()->exists($this->generator->screenshotPath($event->entry))) {
            return;
        }

        $this->generator->cleanup($event->entry);
    }

    /**
     * Remove cached images when Entry is deleted.
     */
    public function handleDeleted(EntryDeleted $event)
    {
        $this->generator->cleanup($event->entry);
    }
}
