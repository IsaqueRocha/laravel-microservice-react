<?php

namespace App\Http\Controllers\Api;

use ReflectionClass;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BasicCrudController extends Controller
{
    protected $paginationSize = 15;

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

    /**
     * Return the model resource collection
     *
     * @return Illuminate\Http\Resources\Json\ResourceCollection
     */
    abstract protected function resourceCollection();

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
        $data = !$this->paginationSize ? $this->model()::all() : $this->model()::paginate($this->paginationSize);

        $resouceCollectionClass = $this->resourceCollection();

        $refClass = new ReflectionClass($this->resourceCollection());

        return $refClass->isSubclassOf(ResourceCollection::class) ?
            new $resouceCollectionClass($data) :
            $resouceCollectionClass::collection($data);
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
