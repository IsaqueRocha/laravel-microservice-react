<?php

namespace Tests\Feature\Http\Controller\Api\VideoController;

use Arr;
use App\Models\Genre;
use App\Models\Video;
use App\Models\Category;
use Tests\Traits\TestSaves;
use Illuminate\Http\Response;
use Tests\Traits\TestResources;
use Tests\Traits\TestValidations;

class VideoControllerCrudTest extends BaseVideoControllerTestCase
{
    use TestValidations;
    use TestSaves;
    use TestResources;

    private const DATA_ID = 'data.id';

    /*
    |--------------------------------------------------------------------------
    | TEST FUNCTIONS
    |--------------------------------------------------------------------------
    */

    private $serializedFields = [
        'id',
        'title',
        'description',
        'year_launched',
        'opened',
        'rating',
        'duration',
        'thumb_file_url',
        'banner_file_url',
        'trailer_file_url',
        'video_file_url',
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
        'genres' => [
            '*' => [
                'id',
                'name',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at',
            ]
        ],
    ];

    // ! POSITIVE TESTS

    public function testIndex()
    {
        $response = $this->json('get', route(self::INDEX));

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
        $this->assertIfFilesUrlExists($this->video, $response);
    }

    public function testShow()
    {
        $response = $this->json('GET', route(self::SHOW, ['video' => $this->video->id]));

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['data' => $this->serializedFields]);

        $this->assertJsonResource($response, $this->resource(), $this->model());
        $this->assertIfFilesUrlExists($this->video, $response);
    }

    public function testSaveWithoutFiles()
    {
        $testData = Arr::except($this->sendData, ['categories_id', 'genres_id']);

        $data = [
            [
                'send_data' => $this->sendData,
                'test_data' => $testData + ['opened' => false]
            ],
            [
                'send_data' => $this->sendData + ['opened' => true],
                'test_data' => $testData + ['opened' => true]
            ],
            [
                'send_data' => $this->sendData + ['rating' => Video::RATING_LIST[1],],
                'test_data' => $testData + ['rating' => Video::RATING_LIST[1]]
            ],
        ];

        foreach ($data as $value) {
            $response = $this->assertStore($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['data' => $this->serializedFields]);

            $this->assertJsonResource($response, $this->resource(), $this->model());
            $this->assertHasCategory($response->json(self::DATA_ID), $value['send_data']['categories_id'][0]);
            $this->assertHasgenre($response->json(self::DATA_ID), $value['send_data']['genres_id'][0]);
            $this->assertIfFilesUrlExists($this->video, $response);


            $response = $this->assertUpdate($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['data' => $this->serializedFields]);

            $this->assertJsonResource($response, $this->resource(), $this->model());
            $this->assertHasCategory($response->json(self::DATA_ID), $value['send_data']['categories_id'][0]);
            $this->assertHasgenre($response->json(self::DATA_ID), $value['send_data']['genres_id'][0]);
        }
    }

    public function testDestroy()
    {
        $response = $this->json('DELETE', route(self::DELETE, ['video' => $this->video->id]));
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::withTrashed()->find($this->video->id));
    }

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
}
