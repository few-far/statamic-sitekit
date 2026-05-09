<?php

namespace FewFar\Sitekit\SocialShare;

use FewFar\Sitekit\ViewModels\Listeners\CreatePageModel;
use HeadlessChromium\Browser\ProcessAwareBrowser;
use HeadlessChromium\BrowserFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Statamic\Contracts\Entries\Entry;

class ImageGenerator
{
    /**
     * Get cached or create a new Social Share image.
     */
    public function image(Entry $entry)
    {
        if ($image = $this->cachedImage($entry)) {
            return $image;
        }

        $image = $this->lock(function () use ($entry) {
            return $this->screenshot($this->htmlUrl($entry))->getRawBinary();
        });

        $this->storage()->put($this->screenshotPath($entry), $image);

        return $image;
    }

    /**
     * Finds the cached version, if enabled.
     */
    public function cachedImage(Entry $entry)
    {
        if (! config('sitekit.social_share.cache.enabled')) {
            return null;
        }

        return $this->storage()->get($this->screenshotPath($entry));
    }

    /**
     * Delete all social share images for all Entries.
     */
    public function clear()
    {
        return $this->storage()->deleteDirectory('social-share/entries/');
    }

    /**
     * Delete all social share images for the given Entry
     */
    public function cleanup(Entry $entry)
    {
        return $this->storage()->deleteDirectory($this->screenshotFolder($entry));
    }

    /**
     * Configured Filesystem via container.
     */
    public function storage()
    {
        return Storage::disk(config('sitekit.social_share.disk'));
    }

    /**
     * Screenshot folder for Entry, relative to storage disk.
     */
    public function screenshotFolder(Entry $entry)
    {
        return 'social-share/entries/' . $entry->id() . '/';
    }

    /**
     * Screenshot path for Entry, relative to storage disk.
     */
    public function screenshotPath(Entry $entry)
    {
        return $this->screenshotFolder($entry) . $this->screenshotHash($entry) . '.webp';
    }

    /**
     * Uses social share data from Entry's mapper to generate a sha1.
     */
    public function screenshotHash(Entry $entry)
    {
        return sha1(json_encode($this->screenshotHashData($entry)));
    }

    /**
     * Use the Entry's mapper to retrieve a signature for the social share image.
     */
    public function screenshotHashData(Entry $entry)
    {
        $mapper = app(CreatePageModel::class)->mapper($entry);

        return [
            $mapper->makeSocialShareViewName(),
            $mapper->makeSocialShareViewData(),
        ];
    }

    /**
     * Create a lock to synchronise image generation.
     *
     * @template T
     *
     * @param null|callable(...$) : T  $callback
     *
     * @return ($callback is null ? \Illuminate\Contracts\Cache\Lock : T)
     */
    public function lock(?callable $callback = null)
    {
        $config = config('sitekit.social_share.lock');
        $lock = Cache::lock($config['name'], $config['expiry_seconds']);

        if (! $callback) {
            return $lock;
        }

        return $lock->block($config['block_seconds'], $callback);
    }

    /**
     * Spawn an instance of Chrome and takes a screenshot of the given URL. Notably, does not
     * acquire a lock, this should be acquired separately.
     */
    public function screenshot(string $url)
    {
        $browser = $this->browser();
        $page = $browser->createPage();

        $page->navigate($url)->waitForNavigation();

        return $page->screenshot([
            'format' => 'webp',
        ]);
    }

    /**
     * Generates the internal render url of for the Entry.
     */
    public function htmlUrl(Entry $entry)
    {
        $host = url('');
        $url = url()->signedRoute('sitekit.social-share.render', [
            'id' => $entry->id(),
        ]);

        $path = str($url)->after($host);

        return config('sitekit.social_share.render_url') . $path;
    }

    /**
     * Creates an instance of Browser using factory.
     */
    public function browser(): ProcessAwareBrowser
    {
        return $this->browserFactory()->createBrowser([
            'noSandbox' => true,
            'windowSize' => explode('x', strval(config('sitekit.social_share.window_size')), 2),
            'customFlags' => [
                '--disable-dev-shm-usage',
                '--disable-extensions',
                '--js-flags="--max-old-space-size=128"',
                '--preprender-from-omnibox',
            ]
        ]);
    }

    /**
     * Create an instance of the Browser factory for Chrome.
     */
    public function browserFactory(): BrowserFactory
    {
        return new BrowserFactory(config('sitekit.social_share.chrome_path'));
    }
}
