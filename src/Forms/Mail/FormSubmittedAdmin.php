<?php

namespace FewFar\Sitekit\Forms\Mail;

use FewFar\Sitekit\Forms\Submission;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Entries\Entry;

class FormSubmittedAdmin extends Mailable
{
    /**
     * Create a new message instance.
     */
    public function __construct(
        public Submission $submission,
        public EntryContract|Entry $form,
    )
    {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Form submitted - ' . $this->form->get('title') . ' - ' . $this->submission->email,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.cms-forms.form-submitted-admin',
            with: [
                'submission' => $this->submission,
                'form' => $this->form,
                'meta' => collect([
                    when(collect($this->submission->meta)->get('url'), function ($url) {
                        return [ 'name' => 'Url', 'value' => $url ];
                    }),
                ])->filter()->values(),
            ],
        );
    }
}
