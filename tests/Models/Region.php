<?php

namespace Fuzz\MagicBox\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    /**
     * @var string
     */
    protected $table = 'regions';

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'label',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var bool
     */
    public $incrementing = false;

}
