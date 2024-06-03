<?php

namespace App\Repositories\IU;

use App\DataObject\IuProductData;
use App\DataObject\Purchases\PurchaseItemTypeData;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Transformers\IU\Product\IuAvailableProductsTransformer;
use App\Transformers\IU\Product\IuProductsTransformer;
use App\Transformers\IU\Product\IuSingleProductTransformer;

class IuProductRepository
{
    private Product $product;

    private PurchaseItem $purchaseItem;

    public function __construct(Product $product, PurchaseItem $purchasedItem)
    {
        // Inject Product
        $this->product = $product;
        $this->purchaseItem = $purchasedItem;
    }

    // Fetch Products
    public function fetchProducts($request)
    {
        // Initiate query for Products
        $productQuery = $this->product->query();

        // Check if $request has category_id
        if ($request->has('category_id')) {
            $productQuery = $productQuery->where('category_id', $request->category_id);
        }

        // Extract filtered products
        $filteredProducts = $productQuery->where('is_available', IuProductData::PRODUCT_AVAILABLE)
            ->with(['category'])
            ->latest()
            ->paginate(10);

        // Transform
        $transformedProducts = fractal($filteredProducts->getCollection(), new IuProductsTransformer());

        // Set collection
        $filteredProducts->setCollection(collect($transformedProducts));

        // Return response
        return response()->json([
            'success' => true,
            'products' => $filteredProducts,
        ]);
    }

    public function fetchAvailableBooks()
    {
        $data = $this->product
            ->where('is_available', 1)
            ->with(['category', 'productMetas'])
            ->latest()
            ->simplePaginate(config('product.pagination'));

        $fractal = fractal($data->getCollection(), new IuAvailableProductsTransformer());
        $data->setCollection(collect($fractal));

        return response()->json($data, 200);
    }

    // Fetch Single Book
    public function fetchSingleBook($request)
    {
        // Fetch Single book
        $book = $this->product
            ->where('id', $request->product_id)
            ->with(['category', 'productMetas'])
            ->firstOrFail();

        // Transformed Response
        $transformedResponse = fractal($book, new IuSingleProductTransformer());

        // Return response
        return response()->json([
            'success' => true,
            'book' => $transformedResponse,
        ]);
    }

    // Fetch single product
    public function fetchSingleProduct($request)
    {
        // Fetch single product
        $product = $this->product->where('id', $request->product_id)
            ->with(['category', 'productMetas'])
            ->firstOrFail();

        // Return response
        return response()->json([
            'success' => true,
            'product' => $product,
        ]);
    }

    public function fetchTopBooks()
    {
        $data = $this->product
            ->select('products.*')
            ->leftJoin('purchase_items', function ($join) {
                $join->on('products.id', '=', 'purchase_items.entity_id')
                    ->where('purchase_items.entity_type', '=', PurchaseItemTypeData::PHYSICAL_PRODUCT);
            })
            ->selectRaw('COUNT(purchase_items.entity_id) as trending')
            ->groupBy('products.id')
            ->latest('trending')
            ->simplePaginate(config('product.pagination'));

        $fractal = fractal($data->getCollection(), new IuSingleProductTransformer());
        $data->setCollection(collect($fractal));

        return response()->json($data, 200);
    }
}
