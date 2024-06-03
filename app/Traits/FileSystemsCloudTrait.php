<?php

namespace App\Traits;

use DateTimeInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait FileSystemsCloudTrait
{
    use UtilsTrait;

    /**
     * Generate signed temporary URL
     */
    private function signedTemporaryUrl(string $path, DateTimeInterface $expiration): string
    {
        return Storage::disk(config('filesystems.cloud'))->temporaryUrl($path, $expiration);
    }

    /**
     * Generate signed S3 URL
     */
    public function generateS3Link(string $file, int $expiry, string $unit = 'H'): string
    {
        $expiration = $this->addTimeToCurrentDate($expiry, $unit);

        return $this->signedTemporaryUrl($file, $expiration);
    }

    /**
     * Generate signed S3 URLs for all files of $directory
     */
    public function generateS3Links(string $directory, int $expiry, string $unit = 'H'): array
    {
        $files = $this->getFiles($directory);
        $expiration = $this->addTimeToCurrentDate($expiry, $unit);

        $signedTemporaryUrls = [];
        foreach ($files as $file) {
            $signedTemporaryUrls[] = $this->signedTemporaryUrl($file, $expiration);
        }

        return $signedTemporaryUrls;
    }

    public function uploadFile($storagePath, $thumbnail)
    {
        $thumbnailName = Str::uuid().'.'.$thumbnail->extension();
        Storage::disk(config('filesystems.cloud'))->putFileAs($storagePath, $thumbnail, $thumbnailName);

        return $thumbnailName;
    }

    public function deleteFile($storagePath, $currentThumbnail)
    {
        Storage::disk(config('filesystems.cloud'))->delete($storagePath.$currentThumbnail);
    }

    public function updateFile($storagePath, $currentThumbnail, $img)
    {
        $this->deleteFile($storagePath, $currentThumbnail);

        return $this->uploadFile($storagePath, $img);
    }

    public function getFiles($storagePath)
    {
        return Storage::disk(config('filesystems.cloud'))->files($storagePath);
    }

    public function getFile($storagePath, $fileName)
    {
        $file = Storage::disk(config('filesystems.cloud'))->get($fileName);
        Storage::disk()->put($storagePath.'/'.$fileName, $file);
    }
}
