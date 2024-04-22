<?php

namespace App\Traits;

use Storage;
use ZipArchive;

trait ZipTrait
{
    public function unzipToCurrentFolder($filePath): bool
    {
        $zip = new ZipArchive;
        if ($zip->open($filePath) === TRUE) {
            $zip->extractTo(substr($filePath, 0, strrpos($filePath, '/')));
            $zip->close();
        } else
            throw new \Exception('Cannot extract zip file');

        return true;
    }

    public function zipToCurrentFolder($dirPath): bool
    {
        $zip = new ZipArchive;
        if (true === ($zip->open(storage_path('app/'. $dirPath . '.zip'), ZipArchive::CREATE | ZipArchive::OVERWRITE))) {
            foreach (Storage::allFiles($dirPath) as $file) {
                $zip->addFile(storage_path('app/' . $file), '/' . basename($file));
            }
            $zip->close();
        } else {
            throw new \Exception('Cannot zip the directory');
        }

        return true;
    }
}
