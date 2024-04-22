<?php

namespace App\DataObject;

class CouponData
{
    // status
    const EXPIRED   = 0;
    const ACTIVE    = 1;
    const INACTIVE  = 2;

    // discount type
    // const FLAT        = 1;
    const PERCENTAGE  = 2;

    // discountable entities
    const ENTITY_TYPES = ['course'];
    const ENTITY_MODEL = ['course' => 'App\Models\Course'];
    const MODEL_ENTITY = ['App\Models\Course' => 'course'];

    static function getStatuses() {
        return [
            self::ACTIVE,
            self::INACTIVE,
            self::EXPIRED
        ];
    }

    static function getDiscountValueTypes() {
        return [
            // self::FLAT,
            self::PERCENTAGE
        ];
    }

    static function getEntityRestrictionTypes() {
        return self::ENTITY_TYPES;
    }
}
