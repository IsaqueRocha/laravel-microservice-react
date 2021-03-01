<?php

namespace Tests\Feature\Http\Controller\Api;

use Mockery;
use Tests\TestCase;
use App\Models\Genre;
use App\Models\Category;
use Tests\Traits\TestSaves;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\Traits\TestResources;
use Tests\Traits\TestValidations;
use Tests\Exceptions\TestException;
use App\Http\Resources\GenreResource;
use App\Http\Controllers\Api\GenreController;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class GenreControllerTest extends TestCase
{
    use TestSaves;
    use TestValidations;
    use DatabaseMigrations;
    use TestResources;

    /*
    |--------------------------------------------------------------------------
    | URL CONSTANTS
    |--------------------------------------------------------------------------
    */

    private const SHOW   = 'genres.show';
    private const INDEX  = 'genres.index';
    private const STORE  = 'genres.store';
    private const UPDATE = 'genres.update';
    private const DELETE = 'genres.destroy';

    private const DATA_ID = 'data.id';

    /** @var Genre $genre */
    private $genre;

    private $serializedFields = [
        'id',
        'name',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at',
        'categories' => [
            '*' => [
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at'
            ]
        ],
    ];
    /*
    |--------------------------------------------------------------------------
    | TEST CONFIGURATION
    |--------------------------------------------------------------------------
    */
    public function setUp(): void
    {
        parent::setUp();
        $this->genre = Genre::factory()->create();
    }

    /*
    |--------------------------------------------------------------------------
    | TEST FUNCTIONS
    |--------------------------------------------------------------------------
    */

    // !POSITIVE TESTS

    public function testIndex()
    {
        $response = $this->json('GET', route(self::INDEX));
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['meta' => ['per_page' => 15]])
            ->assertJsonStructure(
                [
                    'data' => ['*' => $this->serializedFields],
                    'links' => [],
                    'meta'  => []
                ]
            );

        $this->assertJsonCollection($response, $this->resource(), $this->model());
    }

    public function testShow()
    {
        $response = $this->json('GET', route(self::SHOW, ['genre' => $this->genre->id]));
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['data' => $this->serializedFields]);

        $this->assertJsonResource($response, $this->resource(), $this->model());
    }

    public function testStore()
    {
        $categoryID = Category::factory()->create()->id;
        $data = [
            'name' => 'test'
        ];
        $response = $this->assertStore(
            $data + ['categories_id' => [$categoryID]],
            $data + ['is_active' => true, 'deleted_at' => null]
        );
        $response->assertJsonStructure(['data' => $this->serializedFields]);

        $this->assertHasCategory($response->json(self::DATA_ID), $categoryID);

        $this->assertJsonResource($response, $this->resource(), $this->model());

        $data = [
            'name' => 'test',
            'is_active' => false
        ];

        $this->assertStore(
            $data + ['categories_id' => [$categoryID]],
            $data + ['is_active' => false],
        );
    }

    public function testUpdate()
    {
        $categoryID = Category::factory()->create()->id;
        $data = [
            'name' => 'test',
            'is_active' => true
        ];
        $response = $this->assertUpdate(
            $data + ['categories_id' => [$categoryID]],
            $data + ['deleted_at' => null]
        );
        $response->assertJsonStructure(['data' => $this->serializedFields]);

        $this->assertHasCategory($response->json(self::DATA_ID), $categoryID);

        $this->assertJsonResource($response, $this->resource(), $this->model());
    }

    public function testDestroy()
    {
        /** @var Genre $genre  */
        $genre = Genre::factory()->create();

        $response = $this->json('DELETE', route(self::DELETE, ['genre' => $genre->id]));
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertNull(Genre::find($genre->id));
        $this->assertNotNull(Genre::withTrashed()->find($genre->id));
    }

    public function testSyncCategories()
    {
        $categoriesID = Category::factory(3)->create()->pluck('id')->toArray();

        $sendData = [
            'name'          => 'test',
            'categories_id' => [$categoriesID[0]]
        ];

        $response = $this->json('POST', $this->routeStore(), $sendData);
        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoriesID[0],
            'genre_id'    => $response->json(self::DATA_ID)
        ]);

        $sendData = [
            'name'          => 'test',
            'categories_id' => [$categoriesID[1], $categoriesID[2]]
        ];

        $response = $this->json('PUT', $this->routeUpdate(), $sendData);

        $this->assertDatabaseMissing('category_genre', [
            'category_id' => $categoriesID[0],
            'genre_id'    => $response->json(self::DATA_ID)
        ]);

        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoriesID[1],
            'genre_id'    => $response->json(self::DATA_ID)
        ]);

        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoriesID[2],
            'genre_id'    => $response->json(self::DATA_ID)
        ]);
    }

    // ! NEGATIVE TESTS

    public function testInvalidData()
    {
        $data = [
            'name'           => '',
            'categories_id' => ''
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = ['is_active' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');

        $data = ['categories_id' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = ['categories_id' => [100000]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $category = Category::factory()->create();
        $category->delete();
        $data = ['categories_id' => [$category->id]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testRollBack()
    {
        $controller = Mockery::mock(GenreController::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $controller->shouldReceive('validate')->andReturn(['name' => 'test']);
        $controller->shouldReceive('rulesStore')->andReturn([]);
        $controller->shouldReceive('handleRelations')->once()->andThrow(new TestException());

        $request = Mockery::mock(Request::class);

        $hasError = false;
        try {
            $controller->store($request);
        } catch (TestException $e) {
            $this->assertCount(1, Genre::all());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testRollbackUpdate()
    {
        $controller = Mockery::mock(GenreController::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $controller->shouldReceive('findOrFail')->andReturn($this->genre);
        $controller->shouldReceive('validate')->andReturn(['name' => 'test']);
        $controller->shouldReceive('rulesStore')->andReturn([]);
        $controller->shouldReceive('handleRelations')->once()->andThrow(new TestException());

        $request = Mockery::mock(Request::class);

        $hasError = false;
        try {
            $controller->store($request);
        } catch (TestException $e) {
            $this->assertCount(1, Genre::all());
            $hasError = true;
        }

        $this->assertTrue($hasError);
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
        return route(self::UPDATE, ['genre' => $this->genre->id]);
    }

    protected function model()
    {
        return Genre::class;
    }

    protected function resource()
    {
        return GenreResource::class;
    }

    protected function assertHasCategory($genreID, $categoryID)
    {
        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoryID,
            'genre_id' => $genreID
        ]);
    }
}
