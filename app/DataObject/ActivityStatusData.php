<?php


namespace App\DataObject;

use ReflectionClass;

class ActivityStatusData
{
    const IS_ACTIVE = 1;
    const SEMI_ACTIVE_30_TO_90_DAYS = 2;
    const SEMI_ACTIVE_91_TO_270_DAYS = 3;
    const SEMI_ACTIVE_271_TO_365_DAYS = 4;
    const INACTIVE_366_DAYS_OR_MORE = 5;

    const FILTER_FROM_TO = [
        1 => ["from" => 30, "to" => 0],
        2 => ["from" => 90, "to" => 31],
        3 => ["from" => 270, "to" => 91],
        4 => ["from" => 365, "to" => 271],
        5 => ["from" => false, "to" => 366],
    ];

    static function getConstants(): array
    {
        $oClass = new ReflectionClass(__CLASS__);
        $constants = $oClass->getConstants();
        unset($constants['FILTER_FROM_TO']);
        return $constants;
    }
}
