<?php

namespace Tests\Feature\Http\Controller\Api;

use Tests\TestCase;
use App\Models\CastMember;
use Tests\Traits\TestSaves;
use Illuminate\Http\Response;
use Tests\Traits\TestResources;
use Tests\Traits\TestValidations;
use App\Http\Resources\CastMemberResource;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;
    use TestResources;

    /** * @var CastMember  $castMember */
    private $castMember;

    private $serializedFields = [
        'id',
        'type',
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    /*
    |--------------------------------------------------------------------------
    | TEST CONFIGURATION
    |--------------------------------------------------------------------------
    */
    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = CastMember::factory()->create([
            'type' => CastMember::TYPE_DIRECTOR
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | URL CONSTANTS
    |--------------------------------------------------------------------------
    */

    private const SHOW   = 'cast_members.show';
    private const INDEX  = 'cast_members.index';
    private const STORE  = 'cast_members.store';
    private const UPDATE = 'cast_members.update';
    private const DELETE = 'cast_members.destroy';

    /*
    |--------------------------------------------------------------------------
    | TEST FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Test to show all cast members
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->json('GET', route(self::INDEX));
        $response->assertStatus(Response::HTTP_OK)
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

    public function testInvalidData()
    {
        $data = ['name' => '', 'type' => ''];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = ['type' => 's'];
        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    public function testStore()
    {
        $data = [
            [
                'name' => 'test',
                'type' => CastMember::TYPE_DIRECTOR
            ],
            [
                'name' => 'test',
                'type' => CastMember::TYPE_ACTOR
            ]
        ];

        foreach ($data as $value) {
            $response = $this->assertStore($value, $value + ['deleted_at' => null]);
            $response->assertJsonStructure(['data' => $this->serializedFields]);
        }

        $this->assertJsonResource($response, $this->resource(), $this->model());
    }

    public function testUpdate()
    {
        $data = [
            'name' => 'test',
            'type' => CastMember::TYPE_ACTOR
        ];

        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['data' => $this->serializedFields]);

        $this->assertJsonResource($response, $this->resource(), $this->model());
    }

    public function testShow()
    {
        $response = $this->json('GET', route(self::SHOW, ['cast_member' => $this->castMember->id]));
        $response->assertStatus(Response::HTTP_OK);
        $this->assertJsonResource($response, $this->resource(), $this->model());
    }

    public function testDestroy()
    {
        $response = $this->json('DELETE', route(self::DELETE, ['cast_member' => $this->castMember->id]));
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertNull(CastMember::find($this->castMember->id));
        $this->assertNotNull(CastMember::withTrashed()->find($this->castMember->id));
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
        return route(self::UPDATE, ['cast_member' => $this->castMember->id]);
    }

    protected function model()
    {
        return CastMember::class;
    }

    protected function resource()
    {
        return CastMemberResource::class;
    }
}
