<?php

namespace Tests\Feature\Http\Controller\Api\VideoController;

use Storage;
use App\Models\Genre;
use App\Models\Video;
use App\Models\Category;
use Illuminate\Http\Response;
use Tests\Traits\TestUploads;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Tests\Traits\TestValidations;

class VideoControllerUploadTest extends BaseVideoControllerTestCase
{
    use TestValidations;
    use TestUploads;

    public function testInvalidThumbField()
    {
        $this->assertInvalidFile(
            'thumb_file',
            'jpg',
            Video::THUMB_FILE_MAX_SIZE,
            'image'
        );
    }

    public function testInvalidBannerField()
    {
        $this->assertInvalidFile(
            'banner_file',
            'jpg',
            Video::BANNER_FILE_MAX_SIZE,
            'image'
        );
    }

    public function testInvalidTrailerField()
    {
        $this->assertInvalidFile(
            'trailer_file',
            'mp4',
            Video::TRAILER_FILE_MAX_SIZE,
            'mimetypes',
            ['values' => 'video/mp4']
        );
    }

    public function testInvalidVideoField()
    {
        $this->assertInvalidFile(
            'video_file',
            'mp4',
            Video::VIDEO_FILE_MAX_SIZE,
            'mimetypes',
            ['values' => 'video/mp4']
        );
    }

    public function testStoreWithFiles()
    {
        Storage::fake();
        $files = $this->getFiles();

        $response = $this->json(
            'POST',
            $this->routeStore(),
            $this->sendData + $files
        );

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertFilesOnPersist($response, $files);
    }

    public function testUpdateWithFiles()
    {
        Storage::fake();
        $files = $this->getFiles();

        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $this->sendData + $files
        );

        $response->assertStatus(Response::HTTP_OK);
        $this->assertFilesOnPersist($response, $files);

        $newFiles = [
            'thumb_file'    => UploadedFile::fake()->create('new_thumb_file.jpg'),
            'video_file'    => UploadedFile::fake()->create('new_video_file.mp4')
        ];

        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $this->sendData + $newFiles
        );

        $response->assertStatus(Response::HTTP_OK);
        $this->assertFilesOnPersist($response, array_merge($files, $newFiles));

        $id = $response->json('id');

        Storage::assertMissing("$id/{$files['thumb_file']->hashName()}");
        Storage::assertMissing("$id/{$files['video_file']->hashName()}");
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
            'thumb_file'    => UploadedFile::fake()->create('thumb_file.jpg'),
            'banner_file'   => UploadedFile::fake()->create('banner_file.jpg'),
            'trailer_file'  => UploadedFile::fake()->create('trailer_file.mp4'),
            'video_file'    => UploadedFile::fake()->create('video_file.mp4')
        ];
    }

    protected function assertFilesOnPersist(TestResponse $response, $files)
    {
        $id = $response->json('id');
        $video = Video::find($id);
        $this->assertFilesExistsInStorage($video, $files);
    }
}
