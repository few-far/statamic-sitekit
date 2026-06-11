<?php

namespace FewFar\Sitekit\Database\QueryMixins;

use Illuminate\Support\Facades\DB;

class WherePageIsIndexable
{
    public ?string $table = null;

    public function setTable(?string $table)
    {
        $this->table = $table;

        return $table;
    }

    public function __invoke($query)
    {
        $segments = [
            when($this->table, $query->getGrammar()->wrap(...)),
            'COALESCE("data"->>\'page_meta_noindex\', \'\')',
        ];

        $column = collect($segments)->filter()->implode('.');

        return $query->where(DB::raw($column), '!=', 'true');
    }
}
