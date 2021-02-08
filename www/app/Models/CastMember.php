<?php

namespace App\Models;

use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class CastMember extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Uuid;

    public const TYPE_ACTOR = 0;
    public const TYPE_DIRECTOR = 1;

    protected $fillable = ['name', 'type'];

    protected $casts = [
        'id'            => 'string',
        'type'          => 'integer',
        'deleted_at'    => 'datetime'
    ];

    public $incrementing = false;
}
