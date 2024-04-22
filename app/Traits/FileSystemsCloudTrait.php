<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait FileSystemsCloudTrait
{
    use UtilsTrait;

    /**
     * Generate signed temporary URL
     *
     * @param string $path
     * @param  \DateTimeInterface  $expiration
     *
     * @return string
     */
    private function signedTemporaryUrl($path, $expiration): string
    {
        return Storage::disk(config('filesystems.cloud'))->temporaryUrl($path, $expiration);
    }

    /**
     * Generate signed S3 URL
     *
     * @param string $file - S3 object full path
     * @param int $expiry  - signed url validity
     * @param string $unit - expiry unit [Hour(H), Second(S)]
     *
     * @return S3 signed url
     */
    public function generateS3Link($file, $expiry, $unit = 'H'): string
    {
        $expiration = $this->addTimeToCurrentDate($expiry, $unit);
        return $this->signedTemporaryUrl($file, $expiration);
    }

    /**
     * Generate signed S3 URLs for all files of $directory
     *
     * @param string $directory - S3 full path
     * @param int $expiry  - signed url validity
     * @param string $unit - expiry unit [Hour(H), Second(S)]
     *
     * @return S3 signed urls
     */
    public function generateS3Links($directory, $expiry, $unit = 'H'): array
    {
        $files = $this->getFiles($directory);
        $expiration = $this->addTimeToCurrentDate($expiry, $unit);

        $signedTemporaryUrls = array();
        foreach($files as $file)
            $signedTemporaryUrls[] = $this->signedTemporaryUrl($file, $expiration);

        return $signedTemporaryUrls;
    }

    public function uploadFile($storagePath, $thumbnail)
    {
        $thumbnailName = Str::uuid() . "." . $thumbnail->extension();
        Storage::disk(config('filesystems.cloud'))->putFileAs($storagePath, $thumbnail, $thumbnailName);
        return $thumbnailName;
    }

    public function deleteFile($storagePath, $currentThumbnail)
    {
        Storage::disk(config('filesystems.cloud'))->delete($storagePath . $currentThumbnail);
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
        Storage::disk()->put($storagePath . '/' . $fileName, $file);
    }
}
