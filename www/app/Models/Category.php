<?php

namespace App\Models;

use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Uuid;

    protected $fillable = ['name', 'description', 'is_active'];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'id'            => 'string',
        'is_active'     => 'boolean'
    ];

    public $incrementing = false;
}
