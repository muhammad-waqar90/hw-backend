<?php

namespace App\Traits;

use App\Services\AWS\AmazonCloudFrontService;
use Illuminate\Support\Facades\Cookie;

trait CookieTrait
{
    use UrlTrait;

    /**
     * Create a signed Amazon CloudFront cookie for S3 content i.e Lesson videos
     *
     * @param  string  $key:  resource key for S3 content i.e file
     */
    public function generateQueuedSignedCookie($key)
    {
        $url = $this->getUrlRequiredSigned($key);

        $amazonCloudFrontService = new AmazonCloudFrontService();
        $signedCookie = $amazonCloudFrontService->getSignedCookie($url);

        if ($signedCookie) {
            return $this->queuedCookiesForNextResponse($signedCookie);
        }
    }

    /**
     * Queue cookies to send with the next response
     *
     * @method Cookie::queue(
     *
     * @param  string  $name,
     * @param  string  $value  = "",
     * @param  int  $expires_or_options  = 0, (minutes)
     * @param  string  $path  = "",
     * @param  string  $domain  = "",
     * @param  bool  $secure  = false,
     * @param  bool  $httponly  = false
     *                          )
     *
     * @link https://laravel.com/docs/8.x/responses#attaching-cookies-to-responses
     * @link https://www.php.net/manual/en/function.setcookie.php
     */
    public function queuedCookiesForNextResponse($cookies, $expires = 60)
    {
        $host = $this->getHostWithOutSubDomain(config('app.url'));

        foreach ($cookies as $key => $value) {
            Cookie::queue($key, $value, $expires, '/', ".{$host}", true);
        }
    }
}
