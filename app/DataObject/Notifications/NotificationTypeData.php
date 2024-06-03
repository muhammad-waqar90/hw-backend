<?php

namespace App\DataObject\Notifications;

use ReflectionClass;

class NotificationTypeData
{
    const SUPPORT_TICKET = 1;

    const GLOBAL = 2;

    const CERTIFICATE = 3;

    const LESSON_QA_TICKET = 4;

    public static function getConstants()
    {
        $oClass = new ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}
