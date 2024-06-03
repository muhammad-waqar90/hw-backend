<?php

namespace App\Http\Controllers\GU;

use App\Http\Controllers\Controller;
use App\Http\Requests\GU\GuestCart\GuCreateCartRequest;
use App\Http\Requests\GU\GuestCart\GuFetchCombinedCartProductsRequest;
use App\Repositories\GU\GuCartRepository;
use Exception;

class GuCartController extends Controller
{
    // GuestCart
    private GuCartRepository $guCartRepository;

    public function __construct(GuCartRepository $guCartRepository)
    {
        // Inject Repository
        $this->guCartRepository = $guCartRepository;
    }

    // Create Guest Cart
    public function create(GuCreateCartRequest $request)
    {
        try {
            // Logic here
            return $this->guCartRepository->createGuestCart($request);
        } catch (Exception $e) {
            return response()->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // Fetch guest cart
    public function fetch($cartId)
    {
        try {
            // Logic here
            return $this->guCartRepository->fetchGuestCart($cartId);
        } catch (Exception $e) {
            return response()->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // Delete Guest Cart
    public function delete($cartId)
    {
        try {
            // Logic here
            return $this->guCartRepository->deleteGuestCart($cartId);
        } catch (Exception $e) {
            return response()->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // Fetch combined data
    public function fetchCombinedCartProducts(GuFetchCombinedCartProductsRequest $request)
    {

        try {
            // Logic here
            return $this->guCartRepository->fetchCombinedProducts($request);
        } catch (Exception $e) {
            return response()->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
