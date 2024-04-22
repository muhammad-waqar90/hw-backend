<?php

namespace App\Repositories\IU;

use App\DataObject\AF\SalaryScale\SalaryScaleData;
use App\DataObject\DiscountTypeData;
use App\DataObject\Purchases\PurchaseItemTypeData;
use App\Models\Course;
use App\Models\UserSalaryScale;
use App\Models\DiscountedCountry;

class IuSalaryScaleRepository
{
    private DiscountedCountry $discountedCountry;
    private UserSalaryScale $userSalaryScale;
    private Course $course;

    public function __construct(DiscountedCountry $discountedCountry, UserSalaryScale $userSalaryScale, Course $course)
    {
        $this->discountedCountry = $discountedCountry;
        $this->userSalaryScale = $userSalaryScale;
        $this->course = $course;
    }

    public function getDiscountedCountryList()
    {
        return $this->discountedCountry
            ->with('discountRanges')
            ->get()
            ->sortBy('name');
    }

    public function createUserSalaryScale($userId, $discountedCountryId, $discountedCountryRangeId, $declaration)
    {
        return $this->userSalaryScale->create([
            'user_id'                       => $userId,
            'discounted_country_id'         => $discountedCountryId,
            'discounted_country_range_id'   => $discountedCountryRangeId,
            'declaration'                   => $declaration
        ]);
    }

    public function updateUserSalaryScale($userId, $discountedCountryId, $discountedCountryRangeId)
    {
        return $this->userSalaryScale
            ->where('user_id', $userId)
            ->update([
                'discounted_country_id'         => $discountedCountryId,
                'discounted_country_range_id'   => $discountedCountryRangeId
            ]);
    }

    public function applySalaryScaleDiscount($courses, $cartItems, $userSalaryScale)
    {
        foreach ($courses as $course) :

            $cartItem = $cartItems->where('id', $course->id)->where('type', PurchaseItemTypeData::COURSE)->first();

            $salaryScaleDiscountedPrice = array_key_exists(SalaryScaleData::DISCOUNT_INDICATOR, $cartItem);

            // Check if the course salary discount is disabled or enabled
            $courseSalaryScaleDiscountEnabled = (bool)$this->course->isSalaryScaleDiscountEnabled($course->id);

            if ($salaryScaleDiscountedPrice && !$courseSalaryScaleDiscountEnabled) {
                return false;
            }

            if (!$salaryScaleDiscountedPrice && $courseSalaryScaleDiscountEnabled) {
                return false;
            }

            if ($salaryScaleDiscountedPrice) :
                if ($this->isSalaryScaleDiscountApplicable($cartItem, $course->id, SalaryScaleData::DISCOUNT_APPLICABLE_TO)) {
                    if ($salaryScaleDiscountedPrice) {
                        $discount[] = IuPurchaseRepository::makeDiscountItem(
                            DiscountTypeData::SALARY_SCALE,
                            $userSalaryScale->discountedCountryRange->discount_percentage,
                            DiscountTypeData::PERCENTAGE
                        );
                        $course->summary = IuPurchaseRepository::makePurchaseItemSummary($course->price, $discount);

                        $course->price = round((float)$cartItem[SalaryScaleData::DISCOUNT_INDICATOR], 2);
                    }
                } else {
                    return false;
                }
            endif;

        endforeach;

        return $courses;
    }

    private function isSalaryScaleDiscountApplicable($courseFromCart, $entityId, $entityType)
    {

        $hasCourseDiscountKey = array_key_exists(SalaryScaleData::DISCOUNT_INDICATOR, $courseFromCart);

        if ((int)$courseFromCart['id'] === $entityId && $courseFromCart['type'] === $entityType && $hasCourseDiscountKey === true) return true;
    }
}
