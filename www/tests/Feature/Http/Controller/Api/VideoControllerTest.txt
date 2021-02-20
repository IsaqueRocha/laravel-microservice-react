<?php

namespace Tests\Feature\Http\Controller\Api;

use Mockery;
use Tests\TestCase;
use App\Models\Genre;
use App\Models\Video;
use App\Models\Category;
use Tests\Traits\TestSaves;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\Traits\TestUploads;
use Tests\Traits\TestValidations;
use Tests\Exceptions\TestException;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\VideoController;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;
    use TestUploads;

    /** @var Video $video */
    private $video;

    private $sendData;

    /*
    |--------------------------------------------------------------------------
    | TEST CONFIGURATION
    |--------------------------------------------------------------------------
    */
    protected function setUp(): void
    {
        parent::setUp();

        $this->video = Video::factory()->create(['opened' => false]);

        $this->sendData = [
            'title'         => 'title',
            'description'   => 'description',
            'year_launched' => 2010,
            'rating'        => Video::RATING_LIST[0],
            'duration'      => 90
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | URL CONSTANTS
    |--------------------------------------------------------------------------
    */

    private const SHOW   = 'videos.show';
    private const INDEX  = 'videos.index';
    private const STORE  = 'videos.store';
    private const UPDATE = 'videos.update';
    private const DELETE = 'videos.destroy';


    /*
    |--------------------------------------------------------------------------
    | TEST FUNCTIONS
    |--------------------------------------------------------------------------
    */

    // ! POSITIVE TESTS

    public function testIndex()
    {
        $response = $this->json('get', route(self::INDEX));

        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);
    }

    public function testShow()
    {
        $response = $this->json(
            'get',
            route(
                self::SHOW,
                ['video' => $this->video->id]
            )
        );

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson($this->video->toArray());
    }

    public function testSaveWithoutFiles()
    {
        /** @var Category $category */
        $category = Category::factory()->create();
        /** @var Genre $genre */
        $genre = Genre::factory()->create();

        $genre->categories()->sync($category->id);

        $data = [
            [
                'send_data' => $this->sendData + [
                    'categories_id' => [$category->id],
                    'genres_id'     => [$genre->id]
                ],
                'test_data' => $this->sendData + ['opened' => false]
            ],
            [
                'send_data' => $this->sendData + [
                    'opened'        => true,
                    'categories_id' => [$category->id],
                    'genres_id'     => [$genre->id]
                ],
                'test_data' => $this->sendData + ['opened' => true]
            ],
            [
                'send_data' => $this->sendData + [
                    'rating'        => Video::RATING_LIST[1],
                    'categories_id' => [$category->id],
                    'genres_id'     => [$genre->id]
                ],
                'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]]
            ],
        ];

        foreach ($data as $value) {
            $response = $this->assertStore($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);

            $this->assertHasCategory($response->json('id'), $value['send_data']['categories_id'][0]);
            $this->assertHasgenre($response->json('id'), $value['send_data']['genres_id'][0]);

            $response = $this->assertUpdate($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);

            $this->assertHasCategory($response->json('id'), $value['send_data']['categories_id'][0]);
            $this->assertHasgenre($response->json('id'), $value['send_data']['genres_id'][0]);
        }
    }

    public function testStoreWithFiles()
    {
        Storage::fake();
        $files = $this->getFiles();

        /** @var Category $category */
        $category = Category::factory()->create();
        /** @var Genre $genre */
        $genre = Genre::factory()->create();

        $genre->categories()->sync($category->id);

        $response = $this->json(
            'POST',
            $this->routeStore(),
            $this->sendData + [
                'categories_id' => [$category->id],
                'genres_id'     => [$genre->id]
            ] + $files
        );

        $response->assertStatus(Response::HTTP_CREATED);

        $id = $response->json('id');

        foreach ($files as $file) {
            Storage::assertExists("$id/{$file->hashName()}");
        }
    }

    // upload not implemented yet
    public function nottestUpdateWithFiles()
    {
        Storage::fake();
        $files = $this->getFiles();

        /** @var Category $category */
        $category = Category::factory()->create();
        /** @var Genre $genre */
        $genre = Genre::factory()->create();

        $genre->categories()->sync($category->id);

        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $this->sendData + [
                'categories_id' => [$category->id],
                'genres_id'     => [$genre->id]
            ] + $files
        );

        $response->assertStatus(Response::HTTP_OK);

        $id = $response->json('id');

        foreach ($files as $file) {
            Storage::assertExists("$id/{$file->hashName()}");
        }
    }

    public function testDestroy()
    {
        $response = $this->json('DELETE', route(self::DELETE, ['video' => $this->video->id]));
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::withTrashed()->find($this->video->id));
    }

    // public function testSyncCategories()
    // {
    //     $categoriesID = Category::factory(3)->create()->pluck('id')->toArray();
    //     $genre = Genre::factory()->create();
    //     $genre->categories()->sync($categoriesID);
    //     $genresID = $genre->id;

    //     $data = $this->sendData + ['genres_id' => [$genresID], 'categories_id' => [$categoriesID[0]]];

    //     $response = $this->json('POST', $this->routeStore(), $data);

    //     $this->assertDatabaseHas('category_video', [
    //         'category_id' => [$categoriesID[0]],
    //         'video_id'      => $response->json('id'),
    //     ]);

    //     $data = $this->sendData + ['genres_id' => [$genresID], 'categories_id' => [$categoriesID[1], $categoriesID[2]]];

    //     $response = $this->json('PUT', $this->routeUpdate(), $data);

    //     $this->assertDatabaseMissing('category_video', [
    //         'category_id' => [$categoriesID[0]],
    //         'video_id'    => $response->json('id'),
    //     ]);

    //     $this->assertDatabaseHas('category_video', [
    //         'category_id' => [$categoriesID[1]],
    //         'video_id'      => $response->json('id'),
    //     ]);

    //     $this->assertDatabaseHas('category_video', [
    //         'category_id' => [$categoriesID[2]],
    //         'video_id'    => $response->json('id'),
    //     ]);
    // }

    // public function testSyncGenres()
    // {
    //     $genres = Genre::factory(3)->create();
    //     $genresID = $genres->pluck('id')->toArray();
    //     $categoryID = Category::factory()->create()->id;
    //     $genres->each(fn ($genre) => $genre->categories()->sync($categoryID));

    //     $data = $this->sendData + ['genres_id' => [$genresID[0]], 'categories_id' => [$categoryID]];

    //     $response = $this->json('POST', $this->routeStore(), $data);

    //     $this->assertDatabaseHas('genre_video', [
    //         'genre_id' => [$genresID[0]],
    //         'video_id' => $response->json('id'),
    //     ]);

    //     $data = $this->sendData + ['genres_id' => [$genresID[1], $genresID[2]], 'categories_id' => [$categoryID]];

    //     $response = $this->json('PUT', $this->routeUpdate(), $data);

    //     $this->assertDatabaseMissing('genre_video', [
    //         'genre_id' => [$genresID[0]],
    //         'video_id' => $response->json('id'),
    //     ]);

    //     $this->assertDatabaseHas('genre_video', [
    //         'genre_id' => [$genresID[1]],
    //         'video_id' => $response->json('id'),
    //     ]);

    //     $this->assertDatabaseHas('genre_video', [
    //         'genre_id' => [$genresID[2]],
    //         'video_id' => $response->json('id'),
    //     ]);
    // }

    // ! NEGATIVE TESTS

    public function testInvalidRequired()
    {
        $data = [
            'title'         => '',
            'description'   => '',
            'year_launched' => '',
            'rating'        => '',
            'duration'      => '',
            'categories_id' => '',
            'genres_id'     => '',
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
    }

    public function testInvalidMax()
    {
        $data = ['title' => str_repeat('a', 256)];

        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
    }

    public function testInvalidInteger()
    {
        $data = ['duration' => 's'];
        $this->assertInvalidationInStoreAction($data, 'integer');
        $this->assertInvalidationInUpdateAction($data, 'integer');
    }

    public function testInvalidBoolean()
    {
        $data = ['opened' => 's'];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    public function testInvalidYear()
    {
        $data = ['year_launched' => 's'];
        $this->assertInvalidationInStoreAction($data, 'date_format', ['format' => 'Y']);
        $this->assertInvalidationInUpdateAction($data, 'date_format', ['format' => 'Y']);
    }

    public function testInvalidCategoriesIDField()
    {
        $data = ['categories_id' => 's'];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = ['categories_id' => [100]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $category = Category::factory()->create();
        $category->delete();
        $data = ['categories_id' => [$category->id]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testInvalidGenresIDField()
    {
        $data = ['genres_id' => 's'];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = ['genres_id' => [100]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $genre = Genre::factory()->create();
        $genre->delete();
        $data = ['genres_id' => [$genre->id]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testInvalidVideoField()
    {
        $this->assertInvalidFile(
            'video_file',
            'mp4',
            100,
            'mimetypes',
            ['values' => 'video/mp4']
        );
    }

    // public function testRollbackStore()
    // {
    //     $controller = Mockery::mock(VideoController::class)->makePartial()->shouldAllowMockingProtectedMethods();
    //     $controller->shouldReceive('validate')->andReturn($this->sendData);
    //     $controller->shouldReceive('rulesStore')->andReturn([]);
    //     $controller->shouldReceive('handleRelations')->once()->andThrow(new TestException());

    //     $request = Mockery::mock(Request::class);
    //     $request->shouldReceive('get')->andReturn(null);

    //     $hasError = false;

    //     try {
    //         $controller->store($request);
    //     } catch (TestException $e) {
    //         $this->assertCount(1, Video::all());
    //         $hasError = true;
    //     }
    //     $this->assertTrue($hasError);
    // }

    // public function testRollbackUpdate()
    // {
    //     $controller = Mockery::mock(VideoController::class)->makePartial()->shouldAllowMockingProtectedMethods();
    //     $controller->shouldReceive('findOrFail')->andReturn($this->video);
    //     $controller->shouldReceive('validate')->andReturn($this->sendData);
    //     $controller->shouldReceive('rulesStore')->andReturn([]);
    //     $controller->shouldReceive('handleRelations')->once()->andThrow(new TestException());

    //     $request = Mockery::mock(Request::class);
    //     $request->shouldReceive('get')->andReturn(null);

    //     $hasError = false;

    //     try {
    //         $controller->store($request);
    //     } catch (TestException $e) {
    //         $this->assertCount(1, Video::all());
    //         $hasError = true;
    //     }

    //     $this->assertTrue($hasError);
    // }

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
        return route(self::UPDATE, ['video' => $this->video->id]);
    }

    protected function model()
    {
        return Video::class;
    }

    protected function assertHasCategory($videoID, $categoryID)
    {
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoryID,
            'video_id' => $videoID
        ]);
    }
    protected function assertHasGenre($videoID, $genreID)
    {
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genreID,
            'video_id' => $videoID
        ]);
    }

    protected function getFiles()
    {
        return [
            'video_file' => UploadedFile::fake()->create('video_file.mp4')
        ];
    }
}
