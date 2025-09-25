<?php

namespace FewFar\Sitekit\Forms;

use FewFar\Sitekit\Forms\Mail\FormSubmittedAdmin;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Entries\Entry;
use Statamic\Facades;
use Statamic\Facades\GlobalSet;

class SendAdminEmail
{
    use Dispatchable, Queueable;

    public $submission_id;

    public function __construct(
        protected ?Submission $submission,
        protected EntryContract|Entry|null $form = null,
    )
    {
        $this->submission_id = $submission->id;
    }

    /**
     * Laravel can serialise models, but I prefer to avoid this and let the job
     * load it's own data rather than classes getting serialised into the queue.
     * This job might be dispatched immediately however, so might not always be
     * neccessary to load by id.
     */
    protected function ensureModels()
    {
        $this->submission ??= Submission::findOrFail($this->submission_id);
        $this->form ??= Facades\Entry::findOrFail($this->submission->id);
    }

    protected function fallbackEmails()
    {
        /** @var \Statamic\Globals\Variables */
        $variables = GlobalSet::find('site_settings')->inCurrentSite();

        return $variables->get('form_notify_emails');
    }

    protected function emails()
    {
        $to = $this->form->get('notify_emails') ?: $this->fallbackEmails();

        if (! $to) {
            report('No email set in Globals for form submissions.');
        }

        return collect(preg_split('/,\s*/', $to ?? ''))
            ->map(fn ($email) => str($email)->trim()->value())
            ->filter()
            ->values()
            ->all();
    }

    public function handle()
    {
        $this->ensureModels();

        if (! $emails = $this->emails()) {
            return;
        }

        Mail::to($emails)->sendNow($this->mailable());
    }

    public function mailable()
    {
        $this->ensureModels();

        return app(FormSubmittedAdmin::class, [
            'form' => $this->form,
            'submission' => $this->submission,
        ]);
    }
}
