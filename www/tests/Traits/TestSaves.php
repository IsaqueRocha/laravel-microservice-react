<?php

declare(strict_types=1);

namespace Tests\Traits;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;

trait TestSaves
{
    abstract protected function model();
    abstract protected function routeStore();
    abstract protected function routeUpdate();

    protected function assertStore(array $sendData, array $testDatabase, array $testJsonData = []): TestResponse
    {
        $K = 'strval';
        /** @var TestResponse $response */
        $response = $this->json('POST', $this->routeStore(), $sendData);
        if ($response->status() !== Response::HTTP_CREATED) {
            throw new Exception(
                "Response status must be {$K(Response::HTTP_CREATED)}, given {$response->status()}:\n
                {$response->content()}"
            );
        }

        $this->assertInDatabase($response, $testDatabase);
        $this->assertJsonResponseContent($response, $testDatabase, $testJsonData);

        return $response;
    }

    protected function assertUpdate(array $sendData, array $testDatabase, array $testJsonData = []): TestResponse
    {
        $K = 'strval';
        /** @var TestResponse $response */
        $response = $this->json('PUT', $this->routeUpdate(), $sendData);
        if ($response->status() !== Response::HTTP_OK) {
            throw new Exception(
                "Response status must be {$K(Response::HTTP_OK)}, given {$response->status()}:\n
                {$response->content()}"
            );
        }

        $this->assertInDatabase($response, $testDatabase);
        $this->assertJsonResponseContent($response, $testDatabase, $testJsonData);

        return $response;
    }

    private function assertInDatabase(TestResponse $response, array $testDatabase): void
    {
        $model = $this->model();
        $table = (new $model())->getTable();
        $this->assertDatabaseHas($table, $testDatabase + ['id' => $response->json('id')]);
    }

    private function assertJsonResponseContent($response, $testDatabase, $testJsonData): void
    {
        $testResponse = $testJsonData ?? $testDatabase;
        $response->assertJsonFragment($testResponse + ['id' => $response->json('id')]);
    }
}
