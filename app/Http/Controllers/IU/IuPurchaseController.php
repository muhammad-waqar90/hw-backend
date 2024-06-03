<?php

namespace App\Http\Controllers\IU;

use App\DataObject\AF\AdminEmailData;
use App\DataObject\CouponData;
use App\DataObject\PaymentGatewaysData;
use App\DataObject\Purchases\PurchaseHistoryEntityData;
use App\DataObject\Purchases\PurchaseItemTypeData;
use App\DataObject\ShippingData;
use App\Http\Controllers\Controller;
use App\Http\Requests\IU\IuCartCheckoutRequest;
use App\Mail\AF\Order\AfNewOrderConfirmationEmail;
use App\Mail\IU\Purchase\IuPurchaseConfirmationEmail;
use App\Repositories\IU\IuCouponRepository;
use App\Repositories\IU\IuCourseRepository;
use App\Repositories\IU\IuEbookRepository;
use App\Repositories\IU\IuPaymentRepository;
use App\Repositories\IU\IuPurchaseRepository;
use App\Repositories\IU\IuQuizRepository;
use App\Repositories\IU\IuSalaryScaleRepository;
use App\Repositories\IU\IuUserProfileRepository;
use App\Repositories\IU\IuUserRepository;
use App\Services\InApp\InAppService;
use App\Transformers\IU\Purchase\IuPurchaseHistoryTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class IuPurchaseController extends Controller
{
    private IuPurchaseRepository $iuPurchaseRepository;

    private IuCourseRepository $iuCourseRepository;

    private IuPaymentRepository $iuPaymentRepository;

    private IuEbookRepository $iuEbookRepository;

    private IuQuizRepository $iuQuizRepository;

    private IuUserRepository $iuUserRepository;

    private IuCouponRepository $iuCouponRepository;

    private IuSalaryScaleRepository $iuSalaryScaleRepository;

    private IuUserProfileRepository $iuUserProfileRepository;

    public function __construct(
        IuPurchaseRepository $iuPurchaseRepository,
        IuCourseRepository $iuCourseRepository,
        IuPaymentRepository $iuPaymentRepository,
        IuEbookRepository $iuEbookRepository,
        IuQuizRepository $iuQuizRepository,
        IuUserRepository $iuUserRepository,
        IuCouponRepository $iuCouponRepository,
        IuSalaryScaleRepository $iuSalaryScaleRepository,
        IuUserProfileRepository $iuUserProfileRepository
    ) {
        $this->iuPurchaseRepository = $iuPurchaseRepository;
        $this->iuCourseRepository = $iuCourseRepository;
        $this->iuPaymentRepository = $iuPaymentRepository;
        $this->iuEbookRepository = $iuEbookRepository;
        $this->iuQuizRepository = $iuQuizRepository;
        $this->iuUserRepository = $iuUserRepository;
        $this->iuCouponRepository = $iuCouponRepository;
        $this->iuSalaryScaleRepository = $iuSalaryScaleRepository;
        $this->iuUserProfileRepository = $iuUserProfileRepository;
    }

    public function getPurchaseHistory(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $this->iuPurchaseRepository->getPurchaseHistory($request->user()->id);

        $fractal = fractal($data->getCollection(), new IuPurchaseHistoryTransformer)
            ->parseIncludes('purchaseItems');
        $data->setCollection(collect($fractal));

        return response()->json($data, 200);
    }

    /**
     * @throws \Throwable
     */
    public function cartCheckout(IuCartCheckoutRequest $request): \Illuminate\Http\JsonResponse
    {
        // TODO: requried to refactor cartCheckout method
        $user = $this->iuUserRepository->getUser($request->user()->id, true);

        DB::beginTransaction();
        try {
            $items = collect($request->items)->uniqueStrict(function ($item) {
                return $item['id'].$item['type'];
            });

            $courses = $this->iuPurchaseRepository->getCoursesFromCart($items);
            $ebooks = $this->iuPurchaseRepository->getEbooksFromCart($items);
            $exams = $this->iuPurchaseRepository->getExamsFromCart($items);

            // Get physical products from the cart
            $physicalProducts = $this->iuPurchaseRepository->getPhysicalProductsFromCart($items);

            if ($items->count() !== ($courses->count() + $ebooks->count() + $exams->count() + $physicalProducts->count())) {
                return response()->json(['message' => Lang::get('iu.purchases.cart.invalidData')], 422);
            }

            if ($courses->count() !== 0 && $this->iuPurchaseRepository->userOwnsOneOfCourses($courses, $user->id)) {
                return response()->json(['message' => Lang::get('iu.purchases.cart.alreadyOwn')], 400);
            }

            if ($ebooks->count() !== 0 && $this->iuPurchaseRepository->userOwnsOneOfEbooks($ebooks, $user->id)) {
                return response()->json(['message' => Lang::get('iu.purchases.cart.alreadyOwn')], 400);
            }

            if ($exams->count() !== 0 && $this->iuPurchaseRepository->userOwnsOneOfExams($exams, $user->id)) {
                return response()->json(['message' => Lang::get('iu.purchases.cart.alreadyOwn')], 400);
            }

            // validate coupon
            $coupon = $request->code ?: null;
            if ($coupon) {
                $coupon = $this->iuCouponRepository->getCoupon($request->code, CouponData::ACTIVE, true);
                if (! $coupon || ($coupon->redeem_count >= $coupon->redeem_limit)) {
                    return response()->json(['errors' => Lang::get('iu.coupon.canNotRedeem')], 404);
                }
            }

            // apply coupon
            if ($coupon?->restrictions->count()) {
                $courses = $this->iuCouponRepository->applyCouponToCartCourses($coupon, $courses, $items, $user->salaryScale);
                if (! $courses) {
                    return response()->json(['message' => Lang::get('general.salaryScaleDiscountDisabledOrEnabled')], 400);
                }

                // TODO: required for other entities as well.
            } elseif ($courses->count() && $user->salaryScale) {
                // Apply Salary Scale Discount
                $courses = $this->iuSalaryScaleRepository->applySalaryScaleDiscount($courses, $items, $user->salaryScale);
                if (! $courses) {
                    return response()->json(['message' => Lang::get('general.salaryScaleDiscountDisabledOrEnabled')], 400);
                }
            }

            // Apply Book Binding Deduction to Ebooks
            if ($ebooks->count()) {
                $ebooks = $this->iuPurchaseRepository->applyBookBindingDeduction($ebooks, $items);
            }

            $entity = null;

            // Flat shipping rate for physical products only
            $flatShippingRate = $physicalProducts->isEmpty() ? '0.00' : ShippingData::SHIPPING_RATE;

            switch ($request->transactionBy) {
                case PaymentGatewaysData::INAPP:
                    if (! InAppService::verifyReceipt($request->transactionReceipt['transactionReceipt'])) {
                        return response()->json(['message' => Lang::get('iu.purchases.invalidReceipt')], 422);
                    }

                    $entity = $this->handleInAppPayment($request->transactionReceipt);
                    $grandTotal = $courses->sum('tier_price') + $ebooks->sum('price') + $exams->sum('price');
                    break;
                default:
                    $totalPrice = $courses->sum('price') + $ebooks->sum('price') + $exams->sum('price') + $physicalProducts->sum('price');

                    // Add shipping cost to $totalPrice
                    $grandTotal = $totalPrice + $flatShippingRate;
                    $entity = $this->handleStripePayment($user, $grandTotal, $request->paymentMethod);
            }

            $purchaseHistory = $this->iuPurchaseRepository->savePurchaseHistory($user, $grandTotal, $entity['entity_id'], $entity['entity_type']);

            // Save shipping/delivery address in case of physical products
            if (! $physicalProducts->isEmpty()) {
                $this->iuPurchaseRepository->saveDeliveryAddressAndShippingCost($purchaseHistory->id, $request, $user, $flatShippingRate);
                $this->iuPurchaseRepository->saveShippingCost($purchaseHistory->id, PurchaseItemTypeData::SHIPPING);

                //add user profile address in case of incomplete profile address
                $isProfileAddressCompleted = $this->iuUserProfileRepository->getIsProfileAddressCompleted($request->user()->userProfile);
                if (! $isProfileAddressCompleted) {
                    $this->iuUserProfileRepository->updateUserAddress(
                        $user->id,
                        $request->shipping_address,
                        $request->shipping_city,
                        $request->shipping_country,
                        $request->shipping_postal_code
                    );
                }
            }

            foreach ($courses as $item) {
                $this->iuCourseRepository->assignCourseToUser($user->id, $item->id);
                $this->iuPurchaseRepository->savePurchaseItem($purchaseHistory->id, $item, PurchaseItemTypeData::COURSE, $request->transactionBy);
            }
            foreach ($ebooks as $item) {
                $this->iuEbookRepository->assignEbookToUser($user->id, $item->id);
                $this->iuPurchaseRepository->savePurchaseItem($purchaseHistory->id, $item, PurchaseItemTypeData::EBOOK);
            }
            foreach ($exams as $item) {
                $examAccess = $this->iuQuizRepository->assignExamToUser($user->id, $item->id);
                $this->iuPurchaseRepository->savePurchaseItem($purchaseHistory->id, $item, PurchaseItemTypeData::EXAM, $examAccess->id);
            }

            // Save purchased product
            foreach ($physicalProducts as $item) {
                // Save purchase history for physical products
                $this->iuPurchaseRepository->savePurchaseItem($purchaseHistory->id, $item, PurchaseItemTypeData::PHYSICAL_PRODUCT, null, null, true);
            }

            if ($coupon) {
                $this->iuCouponRepository->createCouponPurchaseHistory($coupon->id, $purchaseHistory->id);
                $this->iuCouponRepository->updateCouponRedeemCount($coupon->id);
            }

            DB::commit();

            Mail::to($user->userProfile->email)->queue(new IuPurchaseConfirmationEmail($user, $purchaseHistory->id));

            // Notify admin of a new order in case of $physicalProducts
            if (! $physicalProducts->isEmpty()) {
                // Email books@hijazworld.com
                Mail::to(AdminEmailData::ADMIN_EMAIL)->queue(new AfNewOrderConfirmationEmail(null, $purchaseHistory->id));
            }

            // Introducing a variable $grantedAccessTo with a initial value of NULL
            $grantedAccessToEbooks = null;

            // Grant access to lecture notes
            if (! $physicalProducts->isEmpty()) {
                // Cast output of method grantAccessToLectureNotes() to $accessGranted variable
                $courses = $this->grantAccessToLectureNotes($physicalProducts, $user);
                if (! empty($courses)) {
                    // Mapping the value of $accessGranted to $grantedAccessTo
                    $grantedAccessToEbooks = $courses;
                }
            }

            return response()->json([
                'message' => Lang::get('iu.purchases.cart.checkoutSuccess'),
                'granted_access_to_ebooks' => $grantedAccessToEbooks,
            ], 200);
        } catch (\Stripe\Exception\CardException $cardException) {
            DB::rollback();

            Log::error('Exception: IuPurchaseController@cartCheckout::cardException', [$cardException->getMessage()]);

            return response()->json(['errors' => $cardException->getMessage()], 500);
        } catch (\Stripe\Exception\InvalidRequestException $invalidRequestException) {
            DB::rollback();

            Log::error('Exception: IuPurchaseController@cartCheckout::invalidRequestException', [$invalidRequestException->getMessage()]);

            return response()->json(['errors' => $invalidRequestException->getMessage()], 500);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: IuPurchaseController@cartCheckout', [$e->getMessage()]);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500]), 500]);
        }
    }

    private function handleStripePayment($user, $totalPrice, $paymentMethod)
    {
        $payment = null;
        if ($totalPrice != 0.0) {
            $customer = $this->iuPaymentRepository->updateOrCreateCustomer($user);
            $customer->createOrGetStripeCustomer();
            // $customer->updateStripeCustomer(['email' => $user->userProfile->email]);

            $paymentMethodId = array_key_exists('id', $paymentMethod) ? $paymentMethod['id'] : $customer->defaultPaymentMethod()->id;

            if (array_key_exists('save', $paymentMethod) && $paymentMethod['save']) {
                $customer->updateDefaultPaymentMethod($paymentMethod['id']);
                $customer->updateDefaultPaymentMethodFromStripe();
                $paymentMethodId = $customer->defaultPaymentMethod()->id;
            }

            $payment = $customer->charge(
                $totalPrice * 100,
                $paymentMethodId,
                ['return_url' => config('app.url')]
            );
        }

        $pi = $this->iuPaymentRepository->saveStripePayment($payment);

        return [
            'entity_id' => $pi->id,
            'entity_type' => PurchaseHistoryEntityData::ENTITY_STRIPE_PAYMENT,
        ];
    }

    private function handleInAppPayment($transactionReceipt)
    {
        $pi = $this->iuPaymentRepository->saveInAppPaymentReceipt($transactionReceipt);

        return [
            'entity_id' => $pi->id,
            'entity_type' => PurchaseHistoryEntityData::ENTITY_INAPP_PAYMENT,
        ];
    }

    // Grant access to lecture notes if user is already enrolled in the course
    private function grantAccessToLectureNotes($purchasedBooks, $user)
    {
        /**
         * The variable $moduleCourses represents courses for which the modules are bound with the physical books
         */
        $moduleCourses = [];

        // Iterate through $purchasedBooks array
        foreach ($purchasedBooks as $purchasedBook) {
            // Check if the $purchasedBook is bound to a module
            if (! is_null($purchasedBook->course_module_id)) {
                // Check if $user has access to lecture notes
                if (! $this->iuPurchaseRepository->userHasAccessToLectureNotes($purchasedBook, $user)) {
                    // Grant $user access to lecture notes
                    $this->iuEbookRepository->assignEbookToUser($user->id, $purchasedBook->course_module_id);
                    $course = $this->iuPurchaseRepository->fetchCourseOfModule($purchasedBook->course_module_id, $user);

                    // Add book data to $course
                    $course['book'] = $purchasedBook;

                    // Push $course to $moduleCourses
                    array_push($moduleCourses, $course);
                }
            }
        }

        return $moduleCourses;
    }
}
