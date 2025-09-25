<?php

namespace FewFar\Sitekit\Forms;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $form_id
 * @property string $email
 * @property ?array $values
 * @property ?array $meta
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Submission extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cms_form_submissions';

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
        'values' => 'json',
        'meta' => 'json',
    ];

    /**
     * Cached JSON attribute.
     */
    public function values() : Attribute
    {
        return Attribute::make()->shouldCache();
    }

    /**
     * Cached JSON attribute.
     */
    public function meta() : Attribute
    {
        return Attribute::make()->shouldCache();
    }
}
