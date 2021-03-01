<?php

namespace Tests\Traits;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Testing\TestResponse;

trait TestResources
{
    abstract protected function resource();

    protected function assertResource(TestResponse $response, JsonResource $resource)
    {
        $response->assertJson($resource->response()->getData(true));
    }

    protected function assertJsonResource(TestResponse $response, $resourceClass, $model)
    {
        $id = $response->json('data.id');
        $resource = new $resourceClass($model::find($id));
        $this->assertResource($response, $resource);
    }

    protected function assertJsonCollection(TestResponse $response, $resourceClass, $model)
    {
        $id = $response->json('data.id');
        $resource = $resourceClass::collection(collect($model::find($id)));
        $this->assertResource($response, $resource);
    }
}
