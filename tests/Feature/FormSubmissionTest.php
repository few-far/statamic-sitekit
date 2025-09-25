<?php

use FewFar\Sitekit\Forms\Events\FormSubmitted;
use FewFar\Sitekit\Forms\Mail\FormSubmittedAdmin;
use FewFar\Sitekit\Forms\SendAdminEmail;
use FewFar\Sitekit\Forms\Submission;
use App\Domain\Beacon\ImportPersonFromSubmission;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Statamic\Facades\Entry;

beforeEach(function () {
    $this->artisan('db:seed');

    $this->form = Entry::query()->where('slug', 'contact-us')->first();
    $this->values = collect([
        'email' => 'thomas@few-far.co',
        'name' => 'Thomas Nadin',
        'phone_number' => 'Phone',
        'organisation' => 'thomas@few-far.co',
        'website' => 'thomas@few-far.co',
        'select' => 'option-a',
        'radio' => 'option-a',
        'options' => [ 'option-a' ],
    ]);
});

it('can create submissions for forms and emits events', function (array $values, bool $success) {
    Event::fake();
    Submission::truncate();

    /** @var \Illuminate\Testing\TestResponse */
    $response = $this->postJson(route('submissions.store'), [
        'form' => $this->form->id(),
        'meta' => [ 'url' => '/contact' ],
        'values' => $values,
    ]);

    $submission = Submission::query()
        ->where('form_id', $this->form->id())
        ->first();

    if ($success) {
        $response->assertNoContent();
        Event::assertDispatched(FormSubmitted::class);
        expect($submission)->not->toBe(null);
    } else {
        $response->assertUnprocessable();
        Event::assertNotDispatched(FormSubmitted::class);
        expect($submission)->toBe(null);
    }
})->with([
    [fn () => $this->values->all(), true],
    [fn () => [], false],
    [fn () => $this->values->except('email')->all(), false],
]);

it('dispatches job to email admins', function () {
    Bus::fake();

    FormSubmitted::dispatch($this->form, Submission::first());

    Bus::assertDispatched(SendAdminEmail::class);
});

it('emails admins', function () {
    Mail::fake();

    FormSubmitted::dispatch($this->form, Submission::first());

    Mail::assertSent(FormSubmittedAdmin::class);
});
