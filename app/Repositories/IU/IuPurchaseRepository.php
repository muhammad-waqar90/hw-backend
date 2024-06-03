<?php

namespace App\Repositories\IU;

use App\DataObject\AF\CourseStatusData;
use App\DataObject\BookBindingData;
use App\DataObject\DiscountTypeData;
use App\DataObject\PaymentGatewaysData;
use App\DataObject\Purchases\PurchaseItemStatusData;
use App\DataObject\Purchases\PurchaseItemTypeData;
use App\DataObject\QuizData;
use App\DataObject\ShippingData;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Product;
use App\Models\PurchaseHistory;
use App\Models\PurchaseItem;
use App\Models\Quiz;
use App\Models\ShippingDetail;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class IuPurchaseRepository
{
    private PurchaseHistory $purchaseHistory;

    private Course $course;

    private CourseModule $courseModule;

    private PurchaseItem $purchaseItem;

    private Quiz $quiz;

    private Product $product;

    private ShippingDetail $shippingDetail;

    public function __construct(
        PurchaseHistory $purchaseHistory,
        Course $course,
        CourseModule $courseModule,
        PurchaseItem $purchaseItem,
        Quiz $quiz,
        Product $product,
        ShippingDetail $shippingDetail
    ) {
        $this->purchaseHistory = $purchaseHistory;
        $this->course = $course;
        $this->courseModule = $courseModule;
        $this->purchaseItem = $purchaseItem;
        $this->quiz = $quiz;
        $this->product = $product;
        $this->shippingDetail = $shippingDetail;
    }

    public function getPurchaseHistory($userId)
    {
        return $this->purchaseHistory
            ->where('user_id', $userId)
            ->with(['purchaseItems' => function ($query) {
                $query->select('purchase_items.*');
            }, 'shippingDetails'])
            ->latest('id')
            ->paginate(15);
    }

    public function savePurchaseHistory(User $user, $totalPrice, $entityId, $entityType)
    {
        return $this->purchaseHistory->create([
            'user_id' => $user->id,
            'entity_id' => $entityId,
            'entity_type' => $entityType,
            'amount' => $totalPrice,
        ]);
    }

    public function savePurchaseItem($purchaseHistoryId, $item, $type, $transactionBy = null, $examAccessId = null, $isPhysicalProduct = null)
    {
        return $this->purchaseItem->create([
            'purchase_history_id' => $purchaseHistoryId,
            'course_id' => $item->courseId ?: null,
            'amount' => $transactionBy === PaymentGatewaysData::INAPP ? $item->tier_price : $item->price,
            'summary' => $item->summary ?: null,
            'entity_type' => $type,
            'entity_id' => $examAccessId ?: $item->id,
            'entity_name' => $item->name,
            'status' => PurchaseItemStatusData::PAID,
        ]);
    }

    public function getCoursesFromCart($items)
    {
        $courses = $items->filter(function ($value, $key) {
            return $value['type'] === PurchaseItemTypeData::COURSE;
        });
        if ($courses->isEmpty()) {
            return $courses;
        }

        return $this->course->select('courses.id', 'courses.name', 'courses.price', 'courses.id as courseId', 'tier.value as tier_price')
            ->where('status', CourseStatusData::PUBLISHED)
            ->whereIn('courses.id', $courses->map(function ($item) {
                return $item['id'];
            }))
            ->leftJoin('tiers as tier', 'tier.id', '=', 'courses.tier_id')
            ->get();
    }

    public function getEbooksFromCart($items)
    {

        $ebooks = $items->filter(function ($value, $key) {
            return $value['type'] === PurchaseItemTypeData::EBOOK;
        });

        if ($ebooks->isEmpty()) {
            return $ebooks;
        }

        $courseModules = $this->courseModule->select(
            'course_modules.id',
            'course_modules.name as courseModuleName',
            'course_modules.ebook_price as price',
            'cl.name as courseLevelName',
            'c.name as courseName',
            'c.id as courseId'
        )
            ->whereIn('course_modules.id', $ebooks->map(function ($item) {
                return $item['id'];
            }))
            ->where('course_modules.has_ebook', true)
            ->leftJoin('course_levels as cl', 'cl.id', '=', 'course_modules.course_level_id')
            ->leftJoin('courses as c', 'c.id', '=', 'course_modules.course_id')
            ->get();

        return $courseModules->map(function ($item) {
            $item['name'] = self::generateItemName($item['courseName'], $item['courseLevelName'], $item['courseModuleName']);

            return $item;
        });
    }

    public function getExamsFromCart($items)
    {
        $exams = $items->filter(function ($value, $key) {
            return $value['type'] === PurchaseItemTypeData::EXAM;
        });
        if ($exams->isEmpty()) {
            return $exams;
        }

        $quizTypes = $this->quiz->select('id', 'entity_type')->whereIn('quizzes.id', $exams->map(function ($item) {
            return $item['id'];
        }))->get();

        $moduleExams = $quizTypes->filter(function ($item) {
            return $item->entity_type == QuizData::ENTITY_COURSE_MODULE;
        })->values();

        $levelExams = $quizTypes->filter(function ($item) {
            return $item->entity_type == QuizData::ENTITY_COURSE_LEVEL;
        })->values();

        return self::getLevelQuizzes($levelExams)->merge(self::getModuleQuizzes($moduleExams));
    }

    public function getLevelQuizzes($levelExams)
    {
        $quizzes = $this->quiz->select('quizzes.id', 'quizzes.price as price', 'cl.name as courseLevelName', 'c.name as courseName', 'c.id as courseId')
            ->whereIn('quizzes.id', $levelExams->map(function ($item) {
                return $item['id'];
            }))
            ->where('entity_type', QuizData::ENTITY_COURSE_LEVEL)
            ->leftJoin('course_levels as cl', 'cl.id', '=', 'quizzes.entity_id')
            ->leftJoin('courses as c', 'c.id', '=', 'cl.course_id')
            ->get();

        return $quizzes->map(function ($item) {
            $item['name'] = self::generateItemName($item['courseName'], $item['courseLevelName']);

            return $item;
        });
    }

    public function getModuleQuizzes($moduleExams)
    {
        $quizzes = $this->quiz->select(
            'quizzes.id',
            'cm.name as courseModuleName',
            'quizzes.price as price',
            'cl.name as courseLevelName',
            'c.name as courseName',
            'c.id as courseId'
        )
            ->whereIn('quizzes.id', $moduleExams->map(function ($item) {
                return $item['id'];
            }))
            ->where('entity_type', QuizData::ENTITY_COURSE_MODULE)
            ->leftJoin('course_modules as cm', 'cm.id', '=', 'quizzes.entity_id')
            ->leftJoin('course_levels as cl', 'cl.id', '=', 'cm.course_level_id')
            ->leftJoin('courses as c', 'c.id', '=', 'cm.course_id')
            ->get();

        return $quizzes->map(function ($item) {
            $item['name'] = self::generateItemName($item['courseName'], $item['courseLevelName'], $item['courseModuleName']);

            return $item;
        });
    }

    public function generateItemName($courseName, $levelName, $moduleName = null)
    {
        return $courseName.' - '.$levelName.($moduleName ? ' - '.$moduleName : '');
    }

    public function userOwnsOneOfCourses($courses, $userId): bool
    {
        return DB::table('course_user')->select('course_id', 'user_id')
            ->where('user_id', $userId)
            ->whereIn('course_id', $courses->map(function ($item) {
                return $item['id'];
            }))
            ->exists();
    }

    public function userOwnsOneOfEbooks($ebooks, $userId): bool
    {
        return DB::table('ebook_accesses')->select('course_module_id', 'user_id')
            ->where('user_id', $userId)
            ->whereIn('course_module_id', $ebooks->map(function ($item) {
                return $item['id'];
            }))
            ->exists();
    }

    public function userOwnsOneOfExams($exams, $userId): bool
    {
        return DB::table('exam_accesses')->select('quiz_id', 'user_id')
            ->where('user_id', $userId)
            ->where('attempts_left', '>', 0)
            ->whereIn('quiz_id', $exams->map(function ($item) {
                return $item['id'];
            }))
            ->exists();
    }

    public function updatePurchaseItemStatus($itemId, $status)
    {
        return $this->purchaseItem->where('id', $itemId)
            ->update([
                'status' => $status,
            ]);
    }

    // Get physical books from cart
    public function getPhysicalProductsFromCart($items)
    {
        // Filtering items by type = PHYSICAL_BOOK
        $physicalProducts = $items->filter(function ($value, $key) {
            return $value['type'] === PurchaseItemTypeData::PHYSICAL_PRODUCT;
        });

        // If $physicalProducts is empty
        if ($physicalProducts->isEmpty()) {
            return $physicalProducts;
        }

        // Get physical book
        return $this->product->whereIn('products.id', $physicalProducts->map(function ($item) {
            return $item['id'];
        }))->with(['category'])->get();
    }

    // Save delivery address
    public function saveDeliveryAddressAndShippingCost($purchaseHistoryId, $request, $user, $shippingCost)
    {
        // Initiate empty variables
        $address = '';
        $city = '';
        $country = '';
        $postalCode = '';

        // Get address from user's profile
        if (! $request->different_shipping_address) {

            // Cast $user->userProfile to variable
            $profile = $user->userProfile;

            // Map shipping address variables with the address in user profile
            $address = $profile->address;
            $city = $profile->city;
            $country = $profile->country;
            $postalCode = $profile->postal_code;

        } else {

            // Map shipping address variables if the choose to use a different shipping address
            $address = $request->shipping_address;
            $city = $request->shipping_city;
            $country = $request->shipping_country;
            $postalCode = $request->shipping_postal_code;

        }

        // Save $deliveryAddress to purchase history

        $this->shippingDetail->create([
            'purchase_history_id' => $purchaseHistoryId,
            'user_id' => $user->id,
            'address' => $address,
            'city' => $city,
            'country' => $country,
            'postal_code' => $postalCode,
            'shipping_partner' => null,
            'shipping_cost' => $shippingCost,
        ]);
    }

    // Check if user is enrolled in the course
    public function userHasAccessToCourse($courseId, $user)
    {
        // Check if the $user have access to the course.
        return DB::table('course_user')->select('course_id', 'user_id')
            ->where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->exists();
    }

    // Check if user has access to lecture notes
    public function userHasAccessToLectureNotes($book, $user)
    {
        // Check if $user has access to lecture note
        return DB::table('ebook_accesses')->select('user_id', 'course_module_id')
            ->where('user_id', $user->id)
            ->where('course_module_id', $book->course_module_id)
            ->exists();
    }

    // Fetch course of modules for which access is granted for lecture notes
    public function fetchCourseOfModule($courseModuleId, $user)
    {
        // Get course_id from course module
        $courseId = $this->courseModule->where('id', $courseModuleId)->pluck('course_id');

        // Get course
        $course = $this->course->select('id', 'name')
            ->where('id', $courseId[0])
            ->with(['courseModules' => function ($q) use ($courseModuleId) {
                $q->where('id', $courseModuleId);
            }])
            ->firstOrFail();

        // User's access status of $course
        $isUserEnrolledToCourse = $this->userHasAccessToCourse($course->id, $user);

        // Map user_access_status key to $course
        $course['user_enrolled_to_course'] = $isUserEnrolledToCourse;

        return $course;
    }

    // Binding discount
    public function applyBookBindingDeduction($ebooks, $cartItems)
    {
        foreach ($ebooks as $ebook) {

            if ($this->isBookBindingDiscountApplicable($cartItems, $ebook->id, BookBindingData::TYPE_DEDUCTION_APPLICABLE_TO)) {
                $discount[] = $this->makeDiscountItem(
                    DiscountTypeData::BOOK_BINDING,
                    DiscountTypeData::BOOK_BINDING_DISCOUNT_PERCENTAGE,
                    DiscountTypeData::PERCENTAGE
                );
                $ebook->summary = $this->makePurchaseItemSummary($ebook->price, $discount);

                $ebook->price = 0;
            }

        }

        return $ebooks;
    }

    public function isBookBindingDiscountApplicable($ebooksFromCart, $entityId, $entityType)
    {
        return $ebooksFromCart->contains(function ($ebookFromCart, $key) use ($entityId, $entityType) {
            $hasModuleDiscountKey = array_key_exists(BookBindingData::PRICE_DEDUCTION_INDICATOR, $ebookFromCart);
            if ((int) $ebookFromCart['id'] === $entityId && $ebookFromCart['type'] === $entityType && $hasModuleDiscountKey === true) {
                return true;
            }
        });
    }

    public function saveShippingCost($purchaseHistoryId, $type)
    {
        return $this->purchaseItem->create([
            'purchase_history_id' => $purchaseHistoryId,
            'course_id' => null,
            'amount' => ShippingData::SHIPPING_RATE,
            'entity_type' => $type,
            'entity_id' => null,
            'entity_name' => ShippingData::SHIPPING_TYPES['national'],
            'status' => PurchaseItemStatusData::PAID,
        ]);
    }

    public static function makeDiscountItem($discountType, $value, $valueType)
    {
        return [
            'type' => $discountType,
            'value' => $value,
            'value_type' => $valueType,
        ];
    }

    public static function makePurchaseItemSummary($price, $discount)
    {
        return [
            'price' => $price,
            'discount' => $discount,
        ];
    }
}
