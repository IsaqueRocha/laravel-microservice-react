<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Genre;
use Tests\TestCase;
use App\Models\Video;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class VideoTest extends TestCase
{
    use DatabaseMigrations;

    private $data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->data = [
            'title'         => 'title',
            'description'   => 'description',
            'year_launched' => 2010,
            'rating'        => Video::RATING_LIST[0],
            'duration'      => 90
        ];
    }

    public function testList()
    {
        Video::factory()->create();
        $videos = Video::all();
        $this->assertCount(1, $videos);
        $videosKeys = array_keys($videos->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id',
            'title',
            'description',
            'year_launched',
            'opened',
            'rating',
            'duration',
            'video_file',
            'created_at',
            'updated_at',
            'deleted_at'
        ], $videosKeys);
    }

    public function testCreateWithBasicFields()
    {
        $video = Video::create($this->data);
        $video->refresh();

        $this->assertEquals(36, strlen($video->id));
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => false]);

        $video = Video::create($this->data + ['opened' => true]);
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => true]);
    }

    public function testCreateWithRelations()
    {
        $category = Category::factory()->create();
        $genre = Genre::factory()->create();
        $video = Video::create($this->data + [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id]
        ]);

        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenre($video->id, $genre->id);
    }

    public function testHandleRelations()
    {
        $video = Video::factory()->create();
        Video::handleRelations($video, []);
        $this->assertCount(0, $video->categories()->get());
        $this->assertCount(0, $video->genres()->get());

        $category = Category::factory()->create();
        Video::handleRelations($video, ['categories_id' => [$category->id]]);
        $video->refresh();

        $this->assertCount(1, $video->categories()->get());

        $genre = Genre::factory()->create();
        Video::handleRelations($video, ['genres_id' => [$genre->id]]);
        $video->refresh();

        $this->assertCount(1, $video->genres()->get());

        $video->categories()->delete();
        $video->genres()->delete();

        Video::handleRelations($video, [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id]
        ]);
        $video->refresh();

        $this->assertCount(1, $video->categories()->get());
        $this->assertCount(1, $video->genres()->get());
    }


    public function testSyncCategories()
    {
        $categoriesID = Category::factory(3)->create()->pluck('id')->toArray();
        $video = Video::factory()->create();
        Video::handleRelations($video, ['categories_id' => [$categoriesID[0]]]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => [$categoriesID[0]],
            'video_id'    => $video->id,
        ]);

        Video::handleRelations($video, ['categories_id' => [$categoriesID[1], $categoriesID[2]]]);

        $this->assertDatabaseMissing('category_video', [
            'category_id' => [$categoriesID[0]],
            'video_id'    => $video->id,
        ]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => [$categoriesID[1]],
            'video_id'    => $video->id,
        ]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => [$categoriesID[2]],
            'video_id'    => $video->id,
        ]);
    }

    public function testSyncGenres()
    {
        $genresID = Genre::factory(3)->create()->pluck('id')->toArray();
        $video = Video::factory()->create();
        Video::handleRelations($video, ['genres_id' => [$genresID[0]]]);

        $this->assertDatabaseHas('genre_video', [
            'genre_id' => [$genresID[0]],
            'video_id'    => $video->id,
        ]);

        Video::handleRelations($video, ['genres_id' => [$genresID[1], $genresID[2]]]);

        $this->assertDatabaseMissing('genre_video', [
            'genre_id' => [$genresID[0]],
            'video_id'    => $video->id,
        ]);

        $this->assertDatabaseHas('genre_video', [
            'genre_id' => [$genresID[1]],
            'video_id'    => $video->id,
        ]);

        $this->assertDatabaseHas('genre_video', [
            'genre_id' => [$genresID[2]],
            'video_id'    => $video->id,
        ]);
    }


    public function testRollbackCreate()
    {
        $hasError = false;

        try {
            Video::create([
                'title'         => 'title',
                'description'   => 'description',
                'year_launched' => 2010,
                'rating'        => Video::RATING_LIST[0],
                'duration'      => 90,
                'categories_id' => [0, 1, 2],
            ]);
        } catch (QueryException $e) {
            $this->assertCount(0, Video::all());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testRollbackUpdate()
    {
        $hasError = false;

        $video = Video::factory()->create();
        $title = $video->title;

        try {
            $video->update([
                'title'         => 'title',
                'description'   => 'description',
                'year_launched' => 2010,
                'rating'        => Video::RATING_LIST[0],
                'duration'      => 90,
                'categories_id' => [0, 1, 2],
            ]);
        } catch (QueryException $e) {
            $this->assertDatabaseHas('videos', ['title' => $title]);
            $hasError = true;
        }

        $this->assertTrue($hasError);
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

    public function testDelete()
    {
        /** @var Video $video */
        $video = Video::factory()->create();
        $video->delete();
        $this->assertNull(Video::find($video->id));

        $video->restore();
        $this->assertNotNull(Video::find($video->id));
    }
}
