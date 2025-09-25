<?php

namespace FewFar\Sitekit\Redirects;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Statamic\CP\Column;
use Statamic\Facades\Blueprint;

class RedirectController extends Controller
{
    public function view(Request $request)
    {
        return view()->file(__DIR__.'/resources/views/redirects-index.blade.php', [
            'redirect' => [
                'create_url' => cp_route('redirects.create'),
                'columns' => [
                    Column::make('source'),
                    Column::make('target'),
                    Column::make('code'),
                    Column::make('created_at')->label('Created'),
                    Column::make('source_type')->label('Type')->visible(false),
                    Column::make('updated_at')->label('Updated')->visible(false),
                ],
                'items' => Redirect::query()
                    ->where('group', $request->query('group'))
                    ->orderBy('source', 'asc')
                    ->get()
                    ->map(function ($redirect) {
                        $model = $redirect->only('enabled', 'source_type', 'source', 'target', 'code');

                        $model['created_at'] = $redirect->created_at->format('Y-m-d h:i:s');
                        $model['updated_at'] = $redirect->updated_at->format('Y-m-d h:i:s');
                        $model['edit_url'] = cp_route('redirects.edit', $redirect);
                        $model['delete_url'] = cp_route('redirects.delete', $redirect);
                        return $model;
                    }),
            ],
        ]);
    }

    protected function blueprint()
    {
        return Blueprint::makeFromFields([
            'source_type' => [
                'type' => 'button_group',
                'options' => [
                    'equals' => 'Equals',
                    'regex' => 'Regex',
                ],
                'instructions' => 'Allows for advanced redirects.',
                'default' => 'equals',
                'display' => 'Mode',
                'width' => 25,
            ],
            'code' => [
                'type' => 'select',
                'instructions' => 'Which type of redirect to use.',
                'options' => [
                    '302' => 'Temporary (302)',
                    '301' => 'Permanent (301)',
                ],
                'placeholder' => 'Temporary (302)',
                'default' => '302',
                'display' => 'Code',
                'width' => 25,
            ],
            'group' => [
                'type' => 'text',
                'instructions' => 'Keeps different redirects together in the CMS.',
                'display' => 'Group',
                'width' => 25,
            ],
            'enabled' => [
                'type' => 'toggle',
                'default' => false,
                'width' => 25,
            ],
            'source' => [
                'type' => 'text',
                'display' => 'Source',
                'validate' => 'required|max:2048',
                'instructions' => 'The url or pattern to use for the redirect.',
                'width' => 50,
            ],
            'target' => [
                'type' => 'text',
                'display' => 'Redirect to',
                'validate' => 'required|max:2048',
                'instructions' => 'Where to send the user once the url has been matched.',
                'width' => 50,
            ],
        ]);
    }

    public function create()
    {
        $blueprint = $this->blueprint();
        $fields = $blueprint->fields();

        return view()->file(__DIR__.'/resources/views/redirects-publish.blade.php', [
            'form' => [
                'title' => 'Create Redirect',
                'action' => cp_route('redirects.store'),
                'blueprint' => $blueprint->toPublishArray(),
                'values' => $fields->preProcess()->values(),
                'meta' => $fields->meta(),
            ],
        ]);
    }

    public function edit(Request $request)
    {
        $values = Redirect::findOrFail($request->route('id'))->toArray();
        $blueprint = $this->blueprint();
        $fields = $blueprint->fields()->addValues($values)->preProcess();

        return view()->file(__DIR__.'/resources/views/redirects-publish.blade.php', [
            'form' => [
                'title' => 'Edit Redirect',
                'action' => cp_route('redirects.update', [$values['id']]),
                'blueprint' => $blueprint->toPublishArray(),
                'meta' => $fields->meta(),
                'values' => $fields->values(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $blueprint = $this->blueprint();
        $fields = $blueprint->fields()->addValues($request->all());

        $fields->validate();

        $redirect = Redirect::create($fields->process()->values()->toArray());

        return response()->json([
            'redirect' => cp_route('redirects.edit', $redirect),
        ]);
    }

    public function update(Request $request)
    {
        $redirect = Redirect::findOrFail($request->route('id'));
        $blueprint = $this->blueprint();
        $fields = $blueprint->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values();
        $redirect->update($values->toArray());

        return response()->noContent();
    }

    public function destroy(Request $request)
    {
        $redirect = Redirect::findOrFail($request->route('id'));

        $redirect->delete();

        return response()->noContent();
    }
}
