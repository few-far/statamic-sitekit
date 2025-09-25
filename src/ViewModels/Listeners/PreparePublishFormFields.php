<?php

namespace FewFar\Sitekit\ViewModels\Listeners;

use FewFar\Sitekit\ViewModels\Events\PreparingPublishForm;
use Illuminate\View\View;
use Statamic\Facades\Collection;

class PreparePublishFormFields
{
    public function compose(View $view)
    {
        $data = collect($view->getData());
        $collection = Collection::find($data->get('collection'));
        $blueprint = $collection->entryBlueprint($data->get('blueprint')['handle']);

        event($event = new PreparingPublishForm(
            $view,
            $collection,
            $blueprint,
        ));

        $fields = $blueprint
            ->fields()
            ->addValues($event->fields ?? [])
            ->preProcess();

        $view->with('values', collect($data->get('values'))->merge($fields->values())->all());
        $view->with('meta', $fields->meta());
    }
}
