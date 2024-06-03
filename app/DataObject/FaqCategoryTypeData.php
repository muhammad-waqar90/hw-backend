<?php

namespace App\DataObject;

use ReflectionClass;

class FaqCategoryTypeData
{
    const ROOT_CATEGORY = 1;

    const SUB_CATEGORY = 2;

    public static function getConstants()
    {
        $oClass = new ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}
