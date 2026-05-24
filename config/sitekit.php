<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Social Share image generation
    |--------------------------------------------------------------------------
    |
    | Here you configure how the headless Chrome instance us used to create
    | screenshots of the social share templates. It is possible to cache
    | the images as well as control the lock used in generating them.
    |
    */
    'social_share' => [

        'chrome_path' => env('SITEKIT_SOCIAL_SHARE_CHROME_PATH', '/usr/bin/chromium'),
        'render_url' => env('SITEKIT_SOCIAL_SHARE_RENDER_URL', 'http://localhost:8000'),
        'window_size' => env('SITEKIT_SOCIAL_SHARE_WINDOW_SIZE', '1200x840'),

        'entry_mixins' => [
            \FewFar\Sitekit\Database\QueryMixins\WherePageIsPublished::class,
            \FewFar\Sitekit\Database\QueryMixins\WherePageIsVisible::class,
        ],

        'cache' => [
            'enabled' => env('SITEKIT_SOCIAL_SHARE_CACHE_ENABLED', true),
            'disk' => env('SITEKIT_SOCIAL_SHARE_CACHE_DISK', null),
        ],

        'lock' => [
            'name' => env('SITEKIT_SOCIAL_SHARE_LOCK_NAME', 'sitekit/social-share'),
            'expiry_seconds' => env('SITEKIT_SOCIAL_SHARE_LOCK_EXPIRE', 61),
            'block_seconds' => env('SITEKIT_SOCIAL_SHARE_LOCK_BLOCK', 10),
        ],
    ],
];
