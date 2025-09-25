<?php

namespace FewFar\Sitekit\Redirects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Redirect extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cms_redirects';

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;
}
