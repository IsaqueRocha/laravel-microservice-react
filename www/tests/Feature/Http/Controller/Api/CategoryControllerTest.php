<?php

namespace Tests\Feature\Http\Controller\Api;

use App\Models\Category;
use Carbon\Factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;

    private const SHOW   = 'categories.show';
    private const INDEX  = 'categories.index';
    private const STORE  = 'categories.store';
    private const UPDATE = 'categories.update';
    private const DELETE = 'categories.destroy';

    /**
     * Test to show all categories
     *
     * @return void
     */
    public function testIndex()
    {
        /** @var Category  $category */
        $category = Category::factory()->create();

        $response = $this->json('get', route(self::INDEX));

        $response
            ->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    /**
     * Test to show a single categorie by ID
     *
     * @return void
     */
    public function testShow()
    {
        /** @var Category  $category */
        $category = Category::factory()->create();

        $response = $this->json(
            'get',
            route(
                self::SHOW,
                ['category' => $category->id]
            )
        );

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson($category->toArray());
    }

    public function testInvalidationData()
    {
        $response = $this->json('post', route(self::STORE, []));
        $this->assertInvalidationRequired($response);

        $response = $this->json(
            'post',
            route(
                self::STORE,
                [
                    'name' => str_repeat('a', 256),
                    'is_active' => 'a'
                ]
            )
        );
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);

        $category = Category::factory()->create();

        $response = $this->json(
            'put',
            route(self::UPDATE, ['category' => $category->id]),
            []
        );
        $this->assertInvalidationRequired($response);

        $response = $this->json(
            'put',
            route(
                self::UPDATE,
                ['category' => $category->id]
            ),
            [
                'name' => str_repeat('a', 256),
                'is_active' => 'a'
            ]
        );
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);
    }

    private function assertInvalidationRequired(TestResponse $response)
    {
        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonMissingValidationErrors(['is_active'])
            ->assertJsonFragment([
                \Lang::get('validation.required', ['attribute' => 'name'])
            ]);
    }

    private function assertInvalidationMax(TestResponse $response)
    {
        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                \Lang::get(
                    'validation.max.string',
                    [
                        'attribute' => 'name',
                        'max' => 255
                    ]
                )
            ]);
    }

    private function assertInvalidationBoolean(TestResponse $response)
    {
        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['is_active'])
            ->assertJsonFragment([
                \Lang::get(
                    'validation.boolean',
                    [
                        'attribute' => 'is active'
                    ]
                )
            ]);
    }

    public function testStore()
    {
        $response = $this->json(
            'post',
            route(self::STORE),
            ['name' => 'test1']
        );

        $id = $response->json('id');
        $category = Category::find($id);

        $response
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson($category->toArray());

        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));

        $response = $this->json(
            'post',
            route(self::STORE),
            [
                'name' => 'test1',
                'description' => 'description',
                'is_active' => false
            ]
        );

        $response->assertJsonFragment([
            'description' => 'description',
            'is_active' => false
        ]);
    }

    public function testUpdate()
    {
        $category = Category::factory()->create([
            'description' => 'description',
            'is_active' => false
        ]);
        $response = $this->json(
            'PUT',
            route(self::UPDATE, ['category' => $category->id]),
            [
                'name' => 'test1',
                'description' => 'test_description',
                'is_active' => true
            ]
        );

        $id = $response->json('id');
        $category = Category::find($id);

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson($category->toArray())
            ->assertJsonFragment([
                'description' => 'test_description',
                'is_active' => true
            ]);

        $response = $this->json(
            'PUT',
            route(self::UPDATE, ['category' => $category->id]),
            [
                'name' => 'test1',
                'description' => '',
            ]
        );

        $response->assertJsonFragment(['description' => null]);
    }

    public function testDestroy()
    {
        /** @var Category $category  */
        $category = Category::factory()->create();

        $response = $this->json('DELETE', route(self::DELETE, ['category' => $category->id]));
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertNull(Category::find($category->id));
        $this->assertNotNull(Category::withTrashed()->find($category->id));
    }
}
