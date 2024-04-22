<?php

namespace App\DataObject\AF;

use ReflectionClass;

class EventTypeData
{
    const GLOBAL = 1;
    const NATIONAL = 2;

    static function getEventTypes()
    {
        return [
            self::GLOBAL,
            self::NATIONAL
        ];
    }

    static function getConstants() {
        $oClass = new ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }
}
