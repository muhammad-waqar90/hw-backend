<?php

namespace App\DataObject\Tickets;

use ReflectionClass;

class TicketStatusData
{
    const UNCLAIMED = 1;
    const IN_PROGRESS = 2;
    const RESOLVED = 3;
    const REOPENED = 4;
    const ON_HOLD = 5;

    static function getConstants() {
        $oClass = new ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }
}
