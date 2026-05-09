<?php

namespace FewFar\Sitekit\Database\QueryMixins;

use Illuminate\Support\Facades\DB;

class WherePageIsPublished
{
    public function __invoke($query)
    {
        return $query->whereStatus('published');
    }
}
