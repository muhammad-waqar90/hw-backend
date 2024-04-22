<?php

/*
 * This file is for InApp Payments.
 *
 * (c) Sheeraz Abbas <sheeraz_abbas@yahoo.com>
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

return [

    /*
    |--------------------------------------------------------------------------
    | iOS InApp Authentication Secret - Store Token
    |--------------------------------------------------------------------------
    |
    | Your app’s shared secret, which is a hexadecimal string.
    | For more information about the shared secret,
    | see Generate a Receipt Validation Code [https://help.apple.com/app-store-connect/#/devf341c0f01]
    |
    */

    'secret' => env('APPLE_STORE_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | iOS Web Service URL
    |--------------------------------------------------------------------------
    |
    | Apple app store URL
    | Apple provide differnt base URL for sandbox and review/production environments
    | e.g:
    | SandBox:      https://sandbox.itunes.apple.com
    | production:   https://buy.itunes.apple.com
    |
    */

    'url' => env('APPLE_STORE_URL', 'https://sandbox.itunes.apple.com')

];
