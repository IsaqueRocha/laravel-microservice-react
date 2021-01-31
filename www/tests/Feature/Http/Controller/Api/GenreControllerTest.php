<?php

namespace Tests\Feature\Http\Controller\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

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
}
