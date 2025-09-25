<?php

namespace FewFar\Sitekit\Forms;

use Illuminate\Http\Request;
use Statamic\Facades\Entry;

class ResendAdminEmailController
{
    public function store(Request $request)
    {
        $submission = Submission::findOrFail($request->route('submission'));
        $form = Entry::findOrFail($submission->form_id);

        SendAdminEmail::dispatchSync($submission, $form);

        return response()->noContent();
    }
}
