<?php

namespace FewFar\Sitekit\SocialShare;

use Illuminate\Console\Command;

class CleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitekit:social-share:clear {--force}';

    /**
     * Create an instance of the command.
     */
    public function __construct(
        protected ImageGenerator $images
    )
    {
        parent::__construct();
    }

    /**
     * Run the command.
     */
    public function handle()
    {
        $this->info(' Social Share cache directory exists at:');
        $this->comment(' ' . $this->images->storage()->path('social-share/'));
        $this->newLine();

        if (! $this->shouldRun()) {
            return;
        }

        $this->images->clear();

        $this->info(' Directory removed.');
    }

    /**
     * Get force or ask permission from user.
     */
    protected function shouldRun()
    {
        if ($this->option('force')) {
            return true;
        }

        return $this->confirm('Are you sure you want to delete this directory?');
    }
}
