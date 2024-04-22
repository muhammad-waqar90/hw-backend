<?php

namespace App\DataObject;

use ReflectionClass;

class PermissionData
{
    const FAQ_MANAGEMENT = 1;
    const TICKET_SUBJECT_MANAGEMENT = 2;
    const TICKET_CONTENT_MANAGEMENT = 3;
    const TICKET_SYSTEM_MANAGEMENT = 4;
    const FAQ_CATEGORY_MANAGEMENT = 5;
    const USER_MANAGEMENT = 6;
    const DELETE_USERS = 7;
    const VIEW_USERS_PURCHASE_HISTORY = 8;
    const TICKET_REFUND_MANAGEMENT = 9;
    const REFUNDS_MANAGEMENT = 10;
    const VIEW_REFUNDS = 11;
    const TICKET_TEAM_LEADERSHIP = 12;
    const GLOBAL_NOTIFICATIONS_MANAGEMENT = 13;
    const TICKET_GDPR_MANAGEMENT = 14;
    const GDPR_MANAGEMENT = 15;
    const ADVERT_MANAGEMENT = 16;
    const CATEGORY_MANAGEMENT = 17;
    const EVENT_MANAGEMENT = 18;
    const COURSE_MANAGEMENT = 19;
    const UPDATE_COURSE_STATUS = 20;
    const BULK_UPLOAD_QUIZZES = 21;
    const EBOOK_MANAGEMENT = 22;
    const COUPON_MANAGEMENT = 23;
    const TICKET_LESSON_QA_MANAGEMENT = 24; // Lesson Q&A
    const PHYSICAL_PRODUCT_MANAGEMENT = 25;
    // number 26 is now available for use for any newly created permission
    const SALARY_SCALE_DISCOUNTS_MANAGEMENT = 27;

    static function getConstants()
    {
        $oClass = new ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }
}