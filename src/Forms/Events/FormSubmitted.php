<?php

namespace FewFar\Sitekit\Forms\Events;

use FewFar\Sitekit\Forms\Submission;
use Illuminate\Foundation\Events\Dispatchable;
use Statamic\Contracts\Entries\Entry;

class FormSubmitted
{
    use Dispatchable;

    public function __construct(
        public Entry $form,
        public Submission $submission,
    )
    {
    }
}
