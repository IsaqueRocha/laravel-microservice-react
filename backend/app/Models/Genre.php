<?php

namespace App\Models;

use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Genre extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Uuid;

    protected $fillable = ['name', 'description', 'is_active'];

    protected $casts = [
        'id'            => 'string',
        'is_active'     => 'boolean',
        'deleted_at'    => 'datetime'
    ];

    public $incrementing = false;

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
