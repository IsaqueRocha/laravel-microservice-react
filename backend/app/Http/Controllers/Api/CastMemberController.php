<?php

namespace App\Http\Controllers\Api;

use App\Models\CastMember;
use App\Http\Resources\CastMemberResource;

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

    protected function resourceCollection()
    {
        return $this->resource();
    }

    protected function resource()
    {
        return CastMemberResource::class;
    }
}
