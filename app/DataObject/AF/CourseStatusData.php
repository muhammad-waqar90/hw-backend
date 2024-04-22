<?php

namespace App\DataObject\AF;

use ReflectionClass;

class CourseStatusData
{
    const DRAFT = 1;
    const PUBLISHED = 2;
    const UNPUBLISHED = 3;
    const COMING_SOON = 4;

    static function getConstants() {
        $oClass = new ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }
}
