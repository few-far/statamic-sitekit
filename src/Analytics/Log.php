<?php

namespace FewFar\Sitekit\Analytics;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cms_analytics_logs';

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'json',
    ];

    /**
     * Cached JSON attribute.
     *
     * @return Attribute<array, array>
     */
    public function data() : Attribute
    {
        return Attribute::make()->shouldCache();
    }
}
