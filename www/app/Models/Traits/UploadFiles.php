<?php

namespace App\Models\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait UploadFiles
{
    /**
     * Defines the relative path of the file
     * @return string
     */
    abstract protected function uploadDir();

    /**
     * @param \Illuminate\Http\UploadedFile[] $files
     */
    public function uploadFiles(array $files)
    {
        foreach ($files as $file) {
            $this->uploadFile($file);
        }
    }

    /**
     * @param \Illuminate\Http\UploadedFile $file
     */
    public function uploadFile(UploadedFile $file)
    {
        $file->store($this->uploadDir());
    }

    /**
     * @param array $files
     */
    public function deleteFiles(array $files)
    {
        foreach ($files as $file) {
            $this->deleteFile($file);
        }
    }

    /**
     * @param string|UploadedFile $file
     */
    public function deleteFile($file)
    {
        $filename = $file instanceof UploadedFile ? $file->hashName() : $file;
        Storage::delete("{$this->uploadDir()}/{$filename}");
    }
}
