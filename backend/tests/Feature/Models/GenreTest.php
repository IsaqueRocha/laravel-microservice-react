<?php

namespace Tests\Feature\Models;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test the list of genres.
     *
     * @return void
     */
    public function testList()
    {
        Genre::factory(10)->create();
        $genres = Genre::all();
        $this->assertEquals(10, count($genres));

        $genresKeys = array_keys($genres->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
                'id',
                'name',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at'
            ],
            $genresKeys
        );
    }

    /**
     * Test a new genre creation.
     *
     * @return void
     */
    public function testCreate()
    {
        $genre = Genre::create(['name' => 'test1']);
        $genre->refresh();

        $this->assertEquals('test1', $genre->name);
        $this->assertEquals(36, strlen($genre->id));
        $this->assertTrue($genre->is_active);

        $genre = Genre::create(['name' => 'test2', 'is_active' => false]);

        $this->assertEquals('test2', $genre->name);
        $this->assertFalse($genre->is_active);

        $genre = Genre::create(['name' => 'test3', 'is_active' => true]);

        $this->assertEquals('test3', $genre->name);
        $this->assertTrue($genre->is_active);
    }

    /**
     * Test for updating a existing category.
     *
     * @return void
     */
    public function testUpdate()
    {
        /** @var Genre $genre */
        $genre = Genre::factory()->create()->first();

        $data = [
            'name'      => 'test_name_updated',
            'is_active' => false
        ];

        $genre->update($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $genre->{$key});
        }
    }

    /**
     * Test for deletion of a genre.
     *
     * @return void
     */
    public function testDelete()
    {
        /** @var Genre $genre */
        $genre = Genre::factory()->create();
        $genre->delete();
        $this->assertNull(Genre::find($genre->id));
    }
}
