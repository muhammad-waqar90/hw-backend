<?php

namespace App\Services\AWS;

/*
|----------------------------------------------------------------------------------------------------
| Amazon CloudFront Service
|----------------------------------------------------------------------------------------------------
|
| Amazon CloudFront is a content delivery network (CDN) service built for high performance, security,
| and developer convenience.
|
| Use cases:
| - Deliver fast, secure websites
| - Accelerate dynamic content delivery and APIs
| - Stream live and on-demand video
| - Distribute patches and updates
|
| The configuration options set in to `Aws\Sdk` object will be passed directly to service for $client
|
| Amazon CloudFront Key Features: https://aws.amazon.com/cloudfront/features/
|
*/
use Aws\CloudFront\CloudFrontClient;
use Aws\Laravel\AwsFacade;
use Illuminate\Support\Facades\Log;

/**
 * This service is used to intrect with amazon cloudfront
 *
 * @method __construct()
 * @method getSignedCookie()
 */
class AmazonCloudFrontService
{
    /**
     * @var CloudFrontClient
     */
    private array $cloudFrontClientConfig;

    private CloudFrontClient $client;

    /**
     * @param  array|null  $cloudFrontClientConfig  [region, version]
     * @return CloudFrontClient
     */
    public function __construct(?array $cloudFrontClientConfig = null)
    {
        $this->cloudFrontClientConfig = $cloudFrontClientConfig ?? config('aws.cloudfront.client');
        $this->client = AwsFacade::createClient('cloudfront', $this->cloudFrontClientConfig);
    }

    /**
     * Create a signed Amazon CloudFront cookie.
     *
     * @param string url: URL of the resource being signed (can include query string and wildcards). For example: http://d111111abcdef8.cloudfront.net/images/horizon.jpg?size=large&license=yes
     * @param string policy: JSON policy. Use this option when creating a signed URL for a custom policy.
     * @param int    expires: UTC Unix timestamp used when signing with a canned policy. Not required when passing a custom 'policy' option.
     * @param string key_pair_id: The ID of the key pair used to sign CloudFront URLs for private distributions.
     * @param string private_key: The filepath ot the private key used to sign CloudFront URLs for private distributions.
     * @return array Key => value pairs of signed cookies to set
     *
     * @throws \InvalidArgumentException if url, key_pair_id, or private_key
     *                                   were not specified.
     *
     * @link http://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/WorkingWithStreamingDistributions.html
     */
    public function getSignedCookie(string $url, int $expires = 0, ?string $policy = null)
    {
        $expires = $expires ? $expires : time() + config('aws.cloudfront.signed_cookie_expiry'); // current epoch time - time()
        $policy = $policy ? $policy : $this->getCloudFrontSignedCookiePolicy($url, $expires);

        try {
            return $this->client->getSignedCookie([
                'url' => $url,
                'policy' => $policy,
                'expires' => $expires, // epoch seconds
                'key_pair_id' => config('aws.cloudfront.key_pair_id'),
                'private_key' => config('aws.cloudfront.private_key_path'),
            ]);
        } catch (\Exception $e) {
            Log::error('Exception: AmazonCloudFrontService@getSignedCookie', [$e->getMessage()]);
        }
    }

    /**
     * @return array policy: JSON policy
     */
    public function getCloudFrontSignedCookiePolicy($resourceKey, $epochTime)
    {
        // '{
        //     "Statement": [
        //         {
        //             "Resource": "'.$resourceKey.'",
        //             "Condition": {
        //                 "DateLessThan": {
        //                     "AWS:EpochTime":'.$epochTime.'
        //                 }
        //             }
        //         }
        //     ]
        // }'
        return '{"Statement":[{"Resource":"'.$resourceKey.'","Condition":{"DateLessThan":{"AWS:EpochTime":'.$epochTime.'}}}]}';
    }
}
