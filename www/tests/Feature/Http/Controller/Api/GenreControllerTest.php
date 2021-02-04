<?php

namespace Tests\Feature\Http\Controller\Api;

use Tests\TestCase;
use App\Models\Genre;
use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations;

    private const SHOW   = 'genres.show';
    private const INDEX  = 'genres.index';
    private const STORE  = 'genres.store';
    private const UPDATE = 'genres.update';
    private const DELETE = 'genres.destroy';

    /**
     * Test to show all genres.
     *
     * @return void
     */
    public function testIndex()
    {
        /** @var Genre $genre */
        $genre = Genre::factory()->create();

        $response = $this->json('GET', route(self::INDEX));
        $response->assertStatus(Response::HTTP_OK)->assertJson([$genre->toArray()]);
    }

    /**
     * Test to show a single genre by ID
     *
     * @return void
     */
    public function testShow()
    {
        /** @var Genre $genre */
        $genre = Genre::factory()->create();

        $response = $this->json('GET', route(self::SHOW, ['genre' => $genre->id]));
        $response->assertStatus(Response::HTTP_OK)->assertJson($genre->toArray());
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

        $genre = Genre::factory()->create();

        $response = $this->json(
            'put',
            route(self::UPDATE, ['genre' => $genre->id]),
            []
        );
        $this->assertInvalidationRequired($response);

        $response = $this->json(
            'put',
            route(
                self::UPDATE,
                ['genre' => $genre->id]
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
        $genre = Genre::find($id);

        $response
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson($genre->toArray());

        $this->assertTrue($response->json('is_active'));

        $response = $this->json(
            'post',
            route(self::STORE),
            [
                'name' => 'test1',
                'is_active' => false
            ]
        );

        $response->assertJsonFragment([
            'is_active' => false
        ]);
    }

    public function testUpdate()
    {
        $genre = Genre::factory()->create([
            'is_active' => false
        ]);
        $response = $this->json(
            'PUT',
            route(self::UPDATE, ['genre' => $genre->id]),
            [
                'name' => 'test1',
                'is_active' => true
            ]
        );

        $id = $response->json('id');
        $genre = Genre::find($id);

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson($genre->toArray())
            ->assertJsonFragment([
                'is_active' => true
            ]);

        $response = $this->json(
            'PUT',
            route(self::UPDATE, ['genre' => $genre->id]),
            [
                'name' => 'test1',
            ]
        );

        $response->assertJsonFragment(['name' => 'test1']);
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
}
