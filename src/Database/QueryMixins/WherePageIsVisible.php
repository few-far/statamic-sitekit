<?php

namespace FewFar\Sitekit\Database\QueryMixins;

use Illuminate\Support\Facades\DB;

class WherePageIsVisible
{
    public ?string $table = null;

    public function setTable(?string $table)
    {
        $this->table = $table;

        return $table;
    }

    public function prefix($query)
    {
        if (! $this->table) {
            return '';
        }

        return $query->getGrammar()->wrap($this->table) . '.';
    }

    public function __invoke($query)
    {
        $prefix = $this->prefix($query);

        return $query
            ->where(function ($query) use ($prefix) {
                $query->orWhereRaw($prefix . 'data->\'expiry_redirect\' IS NULL');
                $query->orWhereRaw($prefix . 'data->\'expiry_date\' IS NULL');
                $query->orWhereRaw($prefix . 'data->>\'expiry_date\' > ?', [now()]);
            })
            ->where(DB::raw('COALESCE(' . $prefix . 'data->>\'protect\', \'\')'), '!=', 'password');
    }
}
