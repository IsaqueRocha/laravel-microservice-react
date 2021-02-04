<?php

namespace Tests\Feature\Http\Controller\Api;

use Mockery;
use Tests\TestCase;
use Illuminate\Http\Request;
use Tests\Stubs\Models\CategoryStub;
use Illuminate\Validation\ValidationException;
use Tests\Stubs\Controllers\CategoryControllerStub;

class BasicCrudControllerTest extends TestCase
{
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        CategoryStub::dropTable();
        CategoryStub::createTable();
        $this->controller = new CategoryControllerStub();
    }

    protected function tearDown(): void
    {
        CategoryStub::dropTable();
        parent::tearDown();
    }

    public function testIndex()
    {
        /** @var CategoryStub $category */
        $category = CategoryStub::create([
            'name' => 'test_name',
            'description' => 'test_description'
        ]);
        $this->controller = new CategoryControllerStub();
        $this->assertEquals(
            [$category->toArray()],
            $this->controller->index()->toArray()
        );
    }

    public function testInvalidationInStore()
    {
        $this->expectException(ValidationException::class);
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('all')->once()->andReturn(['name' => '']);
        $this->controller->store($request);
    }

    public function testStore()
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('all')->once()->andReturn(['name' => 'test_name', 'description' => 'test_description']);
        $obj = $this->controller->store($request);
        $this->assertEquals(CategoryStub::find(1)->toArray(), $obj->toArray());
    }
}
