<?php

namespace App\DataObject\Purchases;

use ReflectionClass;

class PurchaseItemTypeData
{
    const COURSE = 'course';

    const EBOOK = 'ebook';

    const EXAM = 'exam_accesses';

    const PHYSICAL_PRODUCT = 'physical_product';

    const SHIPPING = 'shipping'; // TODO: shipping shouldn't be the part of purchase items

    public static function getConstants(): array
    {
        $oClass = new ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }
}
