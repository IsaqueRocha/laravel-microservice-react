<?php

namespace App\Http\Controllers\Api;

use App\Models\CastMember;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CastMemberController extends BasicCrudController
{
    private $rules;

    public function __construct()
    {
        $options = implode(',', [CastMember::TYPE_ACTOR, CastMember::TYPE_DIRECTOR]);

        $this->rules = [
            'name' => 'required',
            'type' => 'required|in:' . $options,
        ];
    }

    protected function model()
    {
        return CastMember::class;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }
}
