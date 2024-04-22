<?php

namespace App\Repositories\AF;

use App\DataObject\CouponData;
use App\Models\CouponRestriction;

class AfCouponRestrictionsRepository
{
    private CouponRestriction $couponRestriction;
    private AfCourseRepository $afCourseRepository;

    public function __construct(CouponRestriction $couponRestriction, AfCourseRepository $afCourseRepository)
    {
        $this->couponRestriction = $couponRestriction;
        $this->afCourseRepository = $afCourseRepository;
    }

    public function createCouponRestrictions($couponId, $entities, $entityType)
    {
        // TODO: we required seperate fun/conditions wrt entity type, for now we are limited to make it for courses only
        $entityModel = CouponData::ENTITY_MODEL[$entityType];
        $courses = $this->afCourseRepository->getCoursesListQuery(null, false, $entities)->get();

        foreach($courses as $course)
            $this->createCouponRestriction($couponId, $course->id, $entityModel);

        return;
    }

    public function createCouponRestriction($couponId, $entityId, $entityType)
    {
        return $this->couponRestriction->create([
            'coupon_id'     => $couponId,
            'entity_id'     => $entityId,
            'entity_type'   => $entityType
        ]);
    }
}
