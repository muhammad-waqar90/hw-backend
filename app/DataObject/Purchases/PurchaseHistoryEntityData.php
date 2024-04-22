<?php


namespace App\DataObject\Purchases;

use App\DataObject\CurrencyData;
use App\DataObject\PaymentGatewaysData;

class PurchaseHistoryEntityData
{
    const ENTITY_STRIPE_PAYMENT = 'App\Models\StripePayment';
    const ENTITY_INAPP_PAYMENT = 'App\Models\InAppPayment';

    const ENTITY_TYPE = [
        self::ENTITY_STRIPE_PAYMENT => ["type" => PaymentGatewaysData::STRIPE, "currency_symbol" => CurrencyData::POUND],
        self::ENTITY_INAPP_PAYMENT  => ["type" => PaymentGatewaysData::INAPP,  "currency_symbol" => CurrencyData::POUND] // we can set base currency into apple store £ as well.
    ];
}
