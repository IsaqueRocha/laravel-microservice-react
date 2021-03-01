<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

abstract class BasicCrudController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ABSTRACT METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Return the respective Model of the controller
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    abstract protected function model();

    /**
     * Return the set of rules to validate a request on store
     *
     * @return array
     */
    abstract protected function rulesStore();

    /**
     * Return the set of rules to validate a request on store
     *
     * @return array
     */
    abstract protected function rulesUpdate();

    /**
     * Return the model resource
     *
     * @return Illuminate\Http\Resources\Json\JsonResource
     */
    abstract protected function resource();

    /*
    |--------------------------------------------------------------------------
    | CRUD API METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->model()::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $obj = $this->model()::create($validatedData);
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $obj = $this->findOrFail($id);
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $obj = $this->findOrFail($id);
        $validatedData = $this->validate($request, $this->rulesUpdate());
        $obj->update($validatedData);
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $obj = $this->findOrFail($id);
        $obj->delete();
        return response()->noContent();
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Return the specified resource, or throw a exception if fails.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function findOrFail($id)
    {
        $model = $this->model();
        $keyName = (new $model())->getRouteKeyName();
        return $this->model()::where($keyName, $id)->firstOrFail();
    }
}
