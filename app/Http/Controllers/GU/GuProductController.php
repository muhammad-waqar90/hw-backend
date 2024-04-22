<?php

namespace App\Http\Controllers\GU;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\GU\Product\GuFetchProductsRequest;
use App\Http\Requests\GU\Product\GuFetchSingleProductRequest;
use App\Repositories\GU\GuProductRepository;

class GuProductController extends Controller
{
    // Import repository
    private GuProductRepository $guProductRepository;

    public function __construct(GuProductRepository $guProductRepository)
    {
        // Inject repository
        $this->guProductRepository = $guProductRepository;
    }

    // Fetch Products
    public function fetch(GuFetchProductsRequest $request)
    {
        try {
            // Logic here
            return $this->guProductRepository->fetchAllProducts($request);
        } catch (Exception $e) {
            return response()->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // Fetch Single Product
    public function singleProduct($id)
    {
        try {
            // Logic here
            return $this->guProductRepository->fetchSingleProduct($id);
        } catch (Exception $e) {
            return response()->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // Fetch by category
    public function fetchByCategory()
    {
        try {
            // Logic here
            return $this->guProductRepository->fetchProductsByCategory();
        } catch (Exception $e) {
        }
    }

    // Fetch Single Category
    public function fetchCategory($id)
    {
        try {
            // Logic here
            return $this->guProductRepository->fetchSingleCategory($id);
        } catch (Exception $e) {
            return response()->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // Top Books
    public function topBooks()
    {
        try {
            // Logic here
            return $this->guProductRepository->fetchTopBooks();
        } catch (Exception $e) {
            return response()->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
