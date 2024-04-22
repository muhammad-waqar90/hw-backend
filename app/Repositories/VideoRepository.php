<?php

namespace App\Repositories;

use App\Traits\CookieTrait;
use Illuminate\Support\Facades\Storage;

class VideoRepository
{
    use CookieTrait;

    public function generateLinkForLesson($file, $cdn = false): string
    {
        // serving video trailer/preview mp4/hls through CDN as well.

        if ($cdn) {
            $this->generateQueuedSignedCookie($file);
            return config('aws.cloudfront.cname') . $file;
        }

        return Storage::disk('s3')->temporaryUrl($file, now()->addMinutes(config('course.video_expiry')));
    }
}
