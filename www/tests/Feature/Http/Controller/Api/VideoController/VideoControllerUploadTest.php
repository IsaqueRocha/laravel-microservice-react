<?php

namespace Tests\Feature\Http\Controller\Api\VideoController;

use Storage;
use App\Models\Genre;
use App\Models\Video;
use App\Models\Category;
use Illuminate\Http\Response;
use Tests\Traits\TestUploads;
use Illuminate\Http\UploadedFile;
use Tests\Traits\TestValidations;

class VideoControllerUploadTest extends BaseVideoControllerTestCase
{
    use TestValidations;
    use TestUploads;

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


    /*
    |--------------------------------------------------------------------------
    | CUSTOM SUPPORT FUNCTIONS
    |--------------------------------------------------------------------------
    */

    protected function getFiles()
    {
        return [
            'video_file' => UploadedFile::fake()->create('video_file.mp4')
        ];
    }
}
