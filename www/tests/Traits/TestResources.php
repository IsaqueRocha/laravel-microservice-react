<?php

namespace Tests\Traits;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Testing\TestResponse;

trait TestResources
{
    abstract protected function resource();

    protected function assertResource(TestResponse $response, JsonResource $resorce)
    {
        $response->assertJson($resorce->response()->getData(true));
    }

    protected function assertJsonResouce(TestResponse $response, $resourceClass, $model)
    {
        $id = $response->json('data.id');
        $resource = new $resourceClass($model::find($id));
        $this->assertResource($response, $resource);
    }
}
