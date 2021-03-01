<?php

namespace Tests\Stubs\Controllers;

use Tests\Stubs\Models\CategoryStub;
use App\Http\Resources\CategoryResource;
use App\Http\Controllers\Api\BasicCrudController;

class CategoryControllerStub extends BasicCrudController
{
    private $rules = [
        'name'        => 'required|max:255',
        'description' => 'nullable',
        'is_active'   => 'boolean'
    ];

    protected function model()
    {
        return CategoryStub::class;
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
        return CategoryResource::class;
    }
}
