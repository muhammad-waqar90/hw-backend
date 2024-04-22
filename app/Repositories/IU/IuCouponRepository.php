<?php

namespace App\Repositories\IU;

use App\Models\Coupon;
use App\Models\Course;
use App\Traits\UtilsTrait;
use App\DataObject\CouponData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\DataObject\AF\CourseStatusData;
use App\Repositories\IU\IuCourseRepository;
use App\DataObject\AF\SalaryScale\SalaryScaleData;
use App\DataObject\DiscountTypeData;
use App\DataObject\Purchases\PurchaseItemTypeData;

class IuCouponRepository
{
    use UtilsTrait;

    private Coupon $coupon;
    private IuCourseRepository $iuCourseRepository;
    private Course $course;

    public function __construct(Coupon $coupon, IuCourseRepository $iuCourseRepository, Course $course)
    {
        $this->coupon = $coupon;
        $this->iuCourseRepository = $iuCourseRepository;
        $this->course = $course;
    }

    public function getCoupon($code, $status = CouponData::ACTIVE, $restrictions = false)
    {
        return $this->coupon
            ->where('code', $code)
            ->where('status', $status)
            ->when($restrictions, function ($query) {
                return $query->with('restrictions');
            })
            ->first();
    }

    public function validateCartItems($cart)
    {
        // TODO: required for other entities as well i.e: ebook, physical book etc
        $validCartItems = $this->iuCourseRepository
            ->getCoursesListQuery(null, false, $cart['course'])
            ->where('status', CourseStatusData::PUBLISHED)
            ->get();

        return $validCartItems->count() !== count($cart['course']);
    }

    public function getCartItemsCanRedeemCoupon($restrictions, $cart)
    {
        // TODO: required for other entities as well i.e: ebook, physical book etc
        return $restrictions->filter(function ($value, $key) use ($cart) {
            return $this->existInArray($value->entity_id, $cart['course']) && $value->entity_type === CouponData::ENTITY_MODEL['course'];
        });
    }

    public function applyCouponToCartCourses($coupon, $courses, $cartItems, $userSalaryScale)
    {
        foreach ($courses as $entity) {
            // Check if course from cart has salaryScaleDiscount key
            $cartItem = $cartItems->where('id', $entity->id)->where('type', PurchaseItemTypeData::COURSE)->first();

            $salaryScaleDiscountIndicator = array_key_exists(SalaryScaleData::DISCOUNT_INDICATOR, $cartItem);

            // Check if the course salary discount is disabled or enabled
            $courseSalaryScaleDiscountEnabled = (bool)$this->course->isSalaryScaleDiscountEnabled($entity->id);

            if (!$salaryScaleDiscountIndicator && $courseSalaryScaleDiscountEnabled) return false;

            if (!$courseSalaryScaleDiscountEnabled && $salaryScaleDiscountIndicator) return false;

            $actualPrice = $entity->price;
            $discount = [];
            if($userSalaryScale && isset($cartItem[SalaryScaleData::DISCOUNT_INDICATOR])) {
                $discount[] = IuPurchaseRepository::makeDiscountItem(
                    DiscountTypeData::SALARY_SCALE,
                    $userSalaryScale->discountedCountryRange->discount_percentage,
                    DiscountTypeData::PERCENTAGE
                );

                // update price to discounted price
                $entity->price = round((float)$cartItem[SalaryScaleData::DISCOUNT_INDICATOR], 2);
            }
            if ($this->canApplyCouponToEntity($coupon->restrictions, $entity->id, CouponData::ENTITY_MODEL['course'])) {
                $discount[] = IuPurchaseRepository::makeDiscountItem(
                    DiscountTypeData::COUPON,
                    $coupon->value,
                    $coupon->value_type
                );

                $entity->price = $this->getEntityDiscountedPrice($coupon->value, $coupon->value_type, $entity->price);
            }

            if(count($discount)) {
                $entity->summary = IuPurchaseRepository::makePurchaseItemSummary($actualPrice, $discount);
            }
        }

        return $courses;
    }

    public function canApplyCouponToEntity($restrictions, $entityId, $entityType)
    {
        return $restrictions->contains(function ($restriction, $key) use ($entityId, $entityType) {
            if ($restriction->entity_id === $entityId && $restriction->entity_type === $entityType) return true;
        });
    }

    public function getEntityDiscountedPrice($discount, $discountType, $price)
    {
        $discountedPrice = $price;
        if ($discountType === CouponData::PERCENTAGE)
            $discountedPrice = $price - ($price * ($discount / 100));

        // if($discountType === CouponData::FLAT)
        //     $discountedPrice = $price - $discount;

        return round($discountedPrice, 2);
    }

    public function createCouponPurchaseHistory($couponId, $purchaseHistoryId)
    {
        return DB::table('coupon_purchase_history')->insert([
            'coupon_id'             => $couponId,
            'purchase_history_id'   => $purchaseHistoryId,
            'created_at'            => Carbon::now(),
            'updated_at'            => Carbon::now()
        ]);
    }

    public function updateCouponRedeemCount($couponId)
    {
        return $this->coupon
            ->where('id', $couponId)
            ->increment('redeem_count');
    }
}
