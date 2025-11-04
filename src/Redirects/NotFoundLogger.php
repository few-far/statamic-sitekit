<?php

namespace FewFar\Sitekit\Redirects;

use FewFar\Sitekit\Exceptions\AppException;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Lottery;

class NotFoundLogger
{
    use InteractsWithNotFoundRequests;

    /**
     * Called by Not Found handler once logger has been prepared.
     *
     * @throws AppException if $request is not set.
     * @throws AppException if $path is not set.
     */
    public function log(): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        $this->record();
        $this->cleanup();
    }

    /**
     * Whether or not the current request should be logged.
     */
    public function shouldLog(): bool
    {
        return ($this->shouldLogCallback() ?? fn () => true)($this);
    }

    /**
     * Stores the current request log.
     */
    protected function record(): void
    {
        $request = $this->request() ?? throw new AppException('request must be defined.');
        $path = $this->path() ?? throw new AppException('path must be defined.');

        DB::table('cms_redirect_logs')->insert([
            'created_at' => DB::raw('NOW()'),
            'updated_at' => DB::raw('NOW()'),
            'url' => $request->getRequestUri(),
            'path' => $path,
            'redirect' => $this->redirect()?->target,
            'redirect_id' => $this->redirect()?->id,
        ]);
    }

    /**
     * Post-action to remove older logs.
     */
    public function cleanup(): void
    {
        if (! $this->shouldCleanup()) {
            return;
        }

        $this->cleanupQuery()->delete();
    }

    /**
     * Determine if the current request should run post-action cleanup.
     */
    public function shouldCleanup(): bool
    {
        return Lottery::odds(1, 100)->choose();
    }

    /**
     * Builds the cleanup DB query.
     */
    public function cleanupQuery(): Builder
    {
        $is_file = static function ($query) {
            // Anything that looks like a file (e.g. .jpeg)
            $query->whereRaw('\'\..+$\' ~* path');
            $query->where('created_at', '<', DB::raw('NOW() - interval \'1 month\''));
        };

        $catchall = static function ($query) {
            $query->where('created_at', '<', DB::raw('NOW() - interval \'1 year\''));
        };

        return DB::table('cms_redirect_logs')
            ->orWhere($is_file)
            ->orWhere($catchall);
    }
}
