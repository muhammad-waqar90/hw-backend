<?php

namespace App\DataObject\Tickets;

use ReflectionClass;

class TicketCategoryData
{
    const SYSTEM = 1;

    const CONTENT = 2;

    const REFUND = 3;

    const GDPR = 4;

    const LESSON_QA = 5;

    public static function getConstants()
    {
        $oClass = new ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}
