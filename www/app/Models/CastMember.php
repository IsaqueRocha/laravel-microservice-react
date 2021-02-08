<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CastMember extends Model
{
    use HasFactory;

    public const TYPE_ACTOR = 0;
    public const TYPE_DIRECTOR = 1;
}
