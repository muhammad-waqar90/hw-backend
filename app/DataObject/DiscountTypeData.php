<?php

namespace App\DataObject;

class DiscountTypeData
{
    // discount types
    const SALARY_SCALE  = 'salary_scale';
    const COUPON        = 'coupon';
    const BOOK_BINDING  = 'book_binding';

    // discount value types
    const FLAT        = 1;
    const PERCENTAGE  = 2;

    const BOOK_BINDING_DISCOUNT_PERCENTAGE  = 100;

    const DISCOUNT_VALUE_TYPES  = [self::FLAT => 'Flat', self::PERCENTAGE => 'Percentage'];
}