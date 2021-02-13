<?php

namespace Tests\Feature\Http\Controller\Api;

use Tests\TestCase;
use App\Models\Video;
use Tests\Traits\TestSaves;
use Illuminate\Http\Response;
use Tests\Traits\TestValidations;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;

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

        $this->video = Video::factory()->create();

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

    public function testSave()
    {
        $data = [
            [
                'send_data' => $this->sendData,
                'test_data' => $this->sendData + ['opened' => false]
            ],
            [
                'send_data' => $this->sendData + ['opened' => true],
                'test_data' => $this->sendData + ['opened' => true]
            ],
            [
                'send_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]],
                'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]]
            ],
        ];

        foreach ($data as $value) {
            $response = $this->assertStore($value['send_data'], $value['send_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);

            $response = $this->assertUpdate($value['send_data'], $value['send_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);
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
    }

    public function testInvalidGenresIDField()
    {
        $data = ['genres_id' => 's'];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = ['genres_id' => [100]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
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
        return route(self::UPDATE, ['video' => $this->video->id]);
    }

    protected function model()
    {
        return Video::class;
    }
}
