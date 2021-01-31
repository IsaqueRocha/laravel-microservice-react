<?php

namespace Tests\Feature\Http\Controller\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Tests\TestCase;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;

    /**
     * @var Category  $category
     */
    private $category;

    /*
    |--------------------------------------------------------------------------
    | TEST CONFIGURATION
    |--------------------------------------------------------------------------
    */
    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::factory()->create();
    }

    /*
    |--------------------------------------------------------------------------
    | URL CONSTANTS
    |--------------------------------------------------------------------------
    */

    private const SHOW   = 'categories.show';
    private const INDEX  = 'categories.index';
    private const STORE  = 'categories.store';
    private const UPDATE = 'categories.update';
    private const DELETE = 'categories.destroy';


    /*
    |--------------------------------------------------------------------------
    | TEST FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Test to show all categories
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->json('get', route(self::INDEX));

        $response
            ->assertStatus(200)
            ->assertJson([$this->category->toArray()]);
    }

    /**
     * Test to show a single categorie by ID
     *
     * @return void
     */
    public function testShow()
    {
        $response = $this->json(
            'get',
            route(
                self::SHOW,
                ['category' => $this->category->id]
            )
        );

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson($this->category->toArray());
    }

    public function testInvalidationData()
    {
        $data = ['name' => ''];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = ['is_active' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
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
        $response = $this->json('DELETE', route(self::DELETE, ['category' => $this->category->id]));
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertNull(Category::find($this->category->id));
        $this->assertNotNull(Category::withTrashed()->find($this->category->id));
    }

    /*
    |--------------------------------------------------------------------------
    | CUSTOM SUPPORT FUNCTIONS
    |--------------------------------------------------------------------------
    */

    protected function routeStore()
    {
        return route(self::STORE);
    }

    protected function routeUpdate()
    {
        return route(self::UPDATE, ['category' => $this->category->id]);
    }
}
