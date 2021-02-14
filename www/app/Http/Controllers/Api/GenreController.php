<?php

namespace App\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class GenreController extends BasicCrudController
{

    private $rules = [
        'name'          => 'required | max:255',
        'is_active'     => 'boolean',
        'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
    ];

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $self = $this;
        $obj = DB::transaction(function () use ($request, $validatedData, $self) {
            $obj = $self->model()::create($validatedData);
            $this->handleRelations($obj, $request);
            return $obj;
        });
        return $obj->refresh();
    }

    public function update(Request $request, $id)
    {
        /** @var Genre $obj */
        $obj = $this->findOrFail($id);
        $self = $this;
        $validatedData = $this->validate($request, $this->rulesUpdate());

        \DB::transaction(function () use ($request, $validatedData, $self, $obj) {
            $obj->update($validatedData);
            $self->handleRelations($obj, $request);
        });

        return $obj;
    }

    protected function handleRelations($video, Request $request)
    {
        $video->categories()->sync($request->get('categories_id'));
    }

    protected function model()
    {
        return Genre::class;
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
