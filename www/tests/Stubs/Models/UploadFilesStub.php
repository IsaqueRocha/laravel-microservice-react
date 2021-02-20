<?php

namespace Tests\Stubs\Models;

use Tests\TestCase;
use App\Models\Traits\UploadFiles;

class UploadFilesStub extends TestCase
{
    use UploadFiles;

    protected static $fileFields = ['file1', 'file2'];

    protected function uploadDir()
    {
        return '1';
    }
}
