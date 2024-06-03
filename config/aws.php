<?php

use Aws\Laravel\AwsServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | AWS SDK Configuration
    |--------------------------------------------------------------------------
    |
    | The configuration options set in this file will be passed directly to the
    | `Aws\Sdk` object, from which all client objects are created. This file
    | is published to the application config directory for modification by the
    | user. The full set of possible options are documented at:
    | http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/configuration.html
    |
    */
    'credentials' => [
        'key' => env('AWS_ACCESS_KEY_ID', ''),
        'secret' => env('AWS_SECRET_ACCESS_KEY', ''),
    ],
    'region' => env('AWS_DEFAULT_REGION'),
    'version' => 'latest',

    /*
    |--------------------------------------------------------------------------
    | AWS SDK Service Client Configuration
    |--------------------------------------------------------------------------
    |
    | region: (string)
    |   A "region" configuration value is required for the services i.e "cloudfront"
    |   service (e.g., "us-west-2"). A list of available public regions and endpoints
    |   can be found at http://docs.aws.amazon.com/general/latest/gr/rande.html.
    |
    | version: (string)
    |   A "version" configuration value is required. Specifying a version constraint
    |   ensures that your code will not be affected by a breaking change made to the
    |   service. For example, when using Amazon S3, you can lock your API version to
    |   "2006-03-01".
    |
    */

    // aws image rekognition service
    'rekognition' => [
        'client' => [
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
        ],
        'bucket' => env('AWS_BUCKET'),
        'minConfidence' => 95,
    ],

    // aws cloudfront service
    /**
     *  Current SDK has the following version(s) of "cloudfront":
     * "2020-05-31"
     * "2019-03-26"
     * "2018-11-05"
     * "2018-06-18"
     * ... 9 more lower versions
     */
    'cloudfront' => [
        'client' => [
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => '2020-05-31',
        ],
        'url' => env('AWS_CLOUDFRONT_URL', ''),
        'cname' => env('AWS_CLOUDFRONT_CNAME_URL', ''),
        'key_pair_id' => env('AWS_CLOUDFRONT_KEY_PAIR_ID', ''),
        'private_key_path' => env('AWS_CLOUDFRONT_PRIVATE_KEY_PATH', ''),
        'signed_cookie_expiry' => env('AWS_CLOUDFRONT_SIGNED_COOKIE_EXPIRY', 3600), // seconds
    ],

    'ua_append' => [
        'L5MOD/'.AwsServiceProvider::VERSION,
    ],
];
