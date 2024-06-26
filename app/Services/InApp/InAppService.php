<?php

namespace App\Services\InApp;

use App\Traits\CurlRequestTrait as curl;

/*
 *
 * Expected Response Codes from iOS inApp
 *
 * If the status code is 0 (zero), your receipt-data is valid.
 *
 * 21000 The App Store could not read the JSON object you provided.
 * 21002 The data in the receipt-data property was malformed or missing.
 * 21003 The receipt could not be authenticated.
 * 21004 The shared secret you provided does not match the shared secret on file for your account.
 * 21005 The receipt server is not currently available.
 * 21006 This receipt is valid but the subscription has expired. When this status code is returned to your server, the receipt data is also decoded and returned as part of the response. Only returned for iOS 6 style transaction receipts for auto-renewable subscriptions.
 * 21007 This receipt is from the test environment, but it was sent to the production environment for verification. Send it to the test environment instead.
 * 21008 This receipt is from the production environment, but it was sent to the test environment for verification. Send it to the production environment instead.
 * 21010 This receipt could not be authorized. Treat this the same as if a purchase was never made.
 * 21100-21199 Internal data access error.
 *
 */

class InAppService
{
    use curl;

    private static $verifyReceipt = '/verifyReceipt';

    private static function url($endpoint)
    {
        return config('inapp.url').$endpoint;
    }

    public static function verifyReceipt($receiptData)
    {
        /**
         * $payload = [
         *  'receipt-data' => 'The Base64-encoded receipt data',
         *  'password' => 'Your app’s shared secret',
         *  'exclude-old-transactions' => 'Set this value to true for the response to include only the latest renewal transaction for any subscriptions. Use this field only for app receipts that contain auto-renewable subscriptions'
         * ];
         */
        $payload = [
            'receipt-data' => $receiptData,
            'password' => config('inapp.secret'),
            'exclude-old-transactions' => false,
        ];

        $response = curl::post(self::url(self::$verifyReceipt), json_encode($payload));
        $receipt = json_decode($response);

        return $receipt->status === 0;
    }
}
