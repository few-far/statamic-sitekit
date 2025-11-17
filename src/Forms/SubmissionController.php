<?php

namespace FewFar\Sitekit\Forms;

use FewFar\Sitekit\Forms\Events\FormSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\View;
use Statamic\Facades\Entry;

class SubmissionController
{
    public function index(Request $request)
    {
        $form = $request->route('entry');
        $submissions = Submission::query()
            ->where('form_id', $form->id())
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate();

        return View::file(__DIR__.'/views/submission-index.blade.php', [
            'form' => $form,
            'submissions' => $submissions,
        ]);
    }

    public function show(Request $request)
    {
        $form = $request->route('entry');
        $submission = Submission::query()
            ->where('form_id', $form->id())
            ->findOrFail($request->route('submission'));

        return View::file(__DIR__.'/views/submission-show.blade.php', [
            'form' => $form,
            'submission' => $submission,
            'meta' => collect([
                [ 'name' => 'Submitted at', 'value' => $submission->created_at->toDateTimeString() ],
                when(collect($submission->meta)->get('url'), function ($url) {
                    return [ 'name' => 'Url', 'value' => $url ];
                }),
            ])->filter()->values(),
        ]);
    }

    /**
     * @param \Statamic\Entries\Entry  $form
     */
    protected function isLikelySpam(Request $request, $form)
    {
        if (data_get($request->input(), 'values.' . $this->honeypotKey($form))) {
            return true;
        }

        $timestamp = rescue(fn () => (
            Date::parse(Crypt::decrypt($request->input('token')))
        ), null, false);

        if (! $timestamp) {
            return true;
        }

        return now()->isBefore($timestamp);
    }

    /**
     * @param \Statamic\Entries\Entry  $form
     */
    protected function honeypotKey($form)
    {
        return $form->get('honeypot_name') ?: 'interesting_fact';
    }

    public function store(Request $request)
    {
        $valid = $request->validate([
            'form' => 'required',
        ]);

        /** @var \Statamic\Entries\Entry */
        $form = Entry::findOrFail($valid['form']);

        $fields = $this->buildFields($form->get('fields'));
        $fields_by_name = $fields
            ->whereNotNull('name')
            ->keyBy(fn ($field) => 'values.' . $field['name']);

        $request->validate(
            [
                'meta.url' => 'string',
                'values' => 'required|array',
                ...$fields_by_name->map->rules->filter()->all(),
            ],
            [],
            $fields_by_name->map->attribute->all(),
        );

        if ($this->isLikelySpam($request, $form)) {
            return response()->noContent();
        }

        $values = collect($request->input('values'));

        $submission = Submission::create([
            'form_id' => $form->id(),
            'email' => $this->guessMostAppropriateEmailFieldValue($request, $form, $fields, $values),
            'meta' => collect($valid['meta'] ?? []),
            'values' => $this->buildValues($fields, $values),
        ]);

        FormSubmitted::dispatch($form, $submission);

        return response()->noContent();
    }


    public function showEmail(Request $request)
    {
        $job = app(SendAdminEmail::class, [
            'form' => $request->route('entry'),
            'submission' => Submission::findOrFail($request->route('submission')),
        ]);

        return $job->mailable();
    }

    /**
     * Maps fields into values that will be stored in the Submission.
     *
     * @param \Illuminate\Support\Collection<string, array>  $fields
     * @param \Illuminate\Support\Collection<string, mixed>  $values
     * @return \Illuminate\Support\Collection<string, array>
     */
    protected function buildValues($fields, $values)
    {
        return $fields->map(function ($field) use ($values) {
            return match ($field['type']) {
                'heading' => $field,
                default => [
                    ...$field,
                    'value' => $values->get($field['name']),
                ],
            };
        });
    }

    /**
     * Creates structure required to validate input from the request.
     *
     * @param \Illuminate\Support\Collection<string, array>  $fields
     * @return \Illuminate\Support\Collection<string, array>
     */
    protected function buildFields($fields)
    {
        return collect($fields)
            ->map('collect')
            ->map(function ($field) {
                if ($field['type'] === 'heading') {
                    return $field;
                }

                return [
                    ...$field,
                    'attribute' => str($field->get('label') ?: $field->get('name'))->replace('_', ' ')->lower(),
                    'rules' => collect()
                        ->when($field->get('required') ?? false)
                        ->push('required')
                        ->push($field->get('validation_rules'))
                        ->filter()
                        ->implode('|'),
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * Individual apps by choose more specific logic to find user's email.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Statamic\Contracts\Entries\Entry  $entry
     * @param \Illuminate\Support\Collection<string, array>  $fields
     * @param \Illuminate\Support\Collection<string, mixed>  $values
     */
    protected function guessMostAppropriateEmailFieldValue(Request $request, $form, $fields, $values) : ?string
    {
        return $values->get('email');
    }
}
