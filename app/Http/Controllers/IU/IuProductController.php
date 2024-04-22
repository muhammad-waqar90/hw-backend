<?php

namespace App\Http\Controllers\IU;

use App\Http\Controllers\Controller;
use App\Http\Requests\IU\Product\IuFetchProductsRequest;
use App\Http\Requests\IU\Product\IuFetchSingleProductRequest;
use App\Repositories\IU\IuProductRepository;
use Exception;

class IuProductController extends Controller
{
    // Product repository
    private IuProductRepository $iuProductRepository;

    public function __construct(IuProductRepository $iuProductRepository)
    {
        // Inject product repository
        $this->iuProductRepository = $iuProductRepository;
    }

    // Fetch Products
    public function fetch(IuFetchProductsRequest $request)
    {
        try {
            // Logic here
            return $this->iuProductRepository->fetchProducts($request);
        } catch (Exception $e) {
            return response()->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    // Fetch available books
    public function availableBooks()
    {
        try {
            // Logic here
            return $this->iuProductRepository->fetchAvailableBooks();
        } catch (Exception $e) {
            return response()->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    // Fetch single product
    public function singleProduct(IuFetchSingleProductRequest $request)
    {
        try {
            // Logic here
            return $this->iuProductRepository->fetchSingleProduct($request);
        } catch (Exception $e) {
            return response()->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    // Fetch single book
    public function singleBook(IuFetchSingleProductRequest $request)
    {
        try {

            // Logic here
            return $this->iuProductRepository->fetchSingleBook($request);
        } catch (Exception $e) {
            return response()->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    // Top Books
    public function topBooks()
    {
        try {
            // Logic here
            return $this->iuProductRepository->fetchTopBooks();
        } catch (Exception $e) {
            return response()->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
