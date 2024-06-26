<?php

namespace App\DataObject;

use ReflectionClass;

class PaymentGatewaysData
{
    const INAPP = 'inapp';

    const STRIPE = 'stripe';

    public static function getConstants()
    {
        $oClass = new ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}
