<?php

namespace Tests\Feature\Http\Controller\Api;

use Tests\TestCase;
use App\Models\Category;
use Tests\Traits\TestSaves;
use Illuminate\Http\Response;
use Tests\Traits\TestResources;
use Tests\Traits\TestValidations;
use App\Http\Resources\CategoryResource;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;
    use TestResources;

    /** @var Category  $category */
    private $category;

    private $serializedFields = [
        'name',
        'description',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

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
    // ! POSITIVE TESTS
    /**
     * Test to show all categories
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->json('GET', route(self::INDEX));

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['meta' => ['per_page' => 15]])
            ->assertJsonStructure(
                [
                    'data'  => ['*' => $this->serializedFields],
                    'links' => [],
                    'meta'  => []
                ]
            );

        $this->assertJsonCollection($response, $this->resource(), $this->model());
    }

    /**
     * Test to show a single categorie by ID
     *
     * @return void
     */
    public function testShow()
    {
        $response = $this->json('GET', route(self::SHOW, ['category' => $this->category->id]));
        $response->assertStatus(Response::HTTP_OK);
        $this->assertJsonResource($response, $this->resource(), $this->model());
    }

    public function testStore()
    {
        $data = ['name' => 'test'];
        $response = $this->assertStore(
            $data,
            $data + ['description' => null, 'is_active' => true, 'deleted_at' => null]
        );
        $response->assertJsonStructure(['data' => $this->serializedFields]);

        $data = [
            'name' => 'test1',
            'description' => 'description',
            'is_active' => false
        ];
        $this->assertStore($data, $data);
        $this->assertJsonResource($response, $this->resource(), $this->model());
    }

    public function testUpdate()
    {
        $this->category = Category::factory()->create([
            'description' => 'description',
            'is_active' => false
        ]);

        $data = [
            'name' => 'test',
            'description' => 'test_description',
            'is_active' => true
        ];
        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['data' => $this->serializedFields]);
        $this->assertJsonResource($response, $this->resource(), $this->model());

        $data = [
            'name' => 'test',
            'description' => ''
        ];
        $this->assertUpdate($data, array_merge($data, ['description' => null]));

        $data['description'] = 'test';
        $this->assertUpdate($data, $data);


        $data['description'] = 'test';
        $this->assertUpdate($data, $data);

        $data['description'] = null;
        $this->assertUpdate($data, $data);
    }

    public function testDestroy()
    {
        $response = $this->json('DELETE', route(self::DELETE, ['category' => $this->category->id]));
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertNull(Category::find($this->category->id));
        $this->assertNotNull(Category::withTrashed()->find($this->category->id));
    }

    // !NEGATIVE TESTS

    public function testInvalidaData()
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

    protected function model()
    {
        return Category::class;
    }

    protected function resource()
    {
        return CategoryResource::class;
    }
}
