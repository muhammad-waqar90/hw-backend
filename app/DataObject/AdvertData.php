<?php

namespace App\DataObject;

class AdvertData
{
    const DEFAULT_ADVERT_EXPIRY_DAYS = 7;

    const STATUS_ACTIVE = 1;

    const STATUS_INACTIVE = 2;

    const DEFAULT_PRIORITY = 100;

    public static function getStatuses()
    {
        return [
            self::STATUS_INACTIVE,
            self::STATUS_ACTIVE,
        ];
    }
}
