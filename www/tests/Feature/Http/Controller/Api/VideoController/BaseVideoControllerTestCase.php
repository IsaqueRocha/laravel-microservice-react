<?php

namespace Tests\Feature\Http\Controller\Api\VideoController;

use App\Models\Category;
use App\Models\Genre;
use Tests\TestCase;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;

abstract class BaseVideoControllerTestCase extends TestCase
{
    use DatabaseMigrations;

    /** @var Video $video */
    protected $video;

    protected $sendData;

    /*
    |--------------------------------------------------------------------------
    | TEST CONFIGURATION
    |--------------------------------------------------------------------------
    */

    protected function setUp(): void
    {
        parent::setUp();

        $this->video = Video::factory()->create(['opened' => false]);

        $category = Category::factory()->create();

        $genre = Genre::factory()->create();

        $genre->categories()->sync($category->id);

        $this->sendData = [
            'title'         => 'title',
            'description'   => 'description',
            'year_launched' => 2010,
            'rating'        => Video::RATING_LIST[0],
            'duration'      => 90,
            'categories_id' => [$category->id],
            'genres_id'     => [$genre->id],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | URL CONSTANTS
    |--------------------------------------------------------------------------
    */

    protected const SHOW   = 'videos.show';
    protected const INDEX  = 'videos.index';
    protected const STORE  = 'videos.store';
    protected const UPDATE = 'videos.update';
    protected const DELETE = 'videos.destroy';

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
}
