<?php

namespace App\Repositories\GU;

use App\Models\Product;
use App\Models\PurchaseItem;
use Illuminate\Http\Response;
use App\Models\Category;
use App\DataObject\Purchases\PurchaseItemTypeData;
use App\Transformers\GU\GuCategoryTransformer;
use App\Transformers\GU\Product\GuProductsTransformer;
use App\Transformers\GU\Product\GuSingleProductTransformer;

class GuProductRepository
{
    // Product Model
    private Product $product;

    // Category Model
    private Category $category;

    // Purchase Item Model
    private PurchaseItem $purchaseItem;

    public function __construct(Product $product, Category $category, PurchaseItem $purchaseItem)
    {
        $this->product = $product;
        $this->category = $category;
        $this->purchaseItem = $purchaseItem;
    }

    // Fetch all products
    public function fetchAllProducts($request)
    {
        // Instance of DB::query
        $productsQuery = $this->product::query();

        // Check if request has param title
        if ($request->has('name')) {

            // Filter books by title
            $productsQuery = $productsQuery->where('name', 'like', '%' . $request->name . '%');
        }

        // Check if request has param author
        if ($request->has('category')) {

            // Filter books by author
            $productsQuery = $productsQuery->whereHas('category', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->category . '%');
            });
        }

        // Check if request has price
        if ($request->has('price')) {

            // Filter books by price
            $productsQuery = $productsQuery->where('price', $request->price);
        }

        // Check if request has is_available
        if ($request->has('is_available')) {

            // Filter by availability
            $productsQuery = $productsQuery->where('is_available', $request->is_available);
        }

        // Check if request has is_bounded
        if ($request->has('is_not_bounded')) {

            // Filter by course_module_id not null
            $productsQuery = $productsQuery->whereNull('course_module_id');
        }

        // Filtered books
        $filteredProducts = $productsQuery->with(['category'])->latest()->paginate(10);

        // Transform $filterProducts
        $transformedProducts = fractal($filteredProducts->getCollection(), new GuProductsTransformer());

        // Set collection
        $filteredProducts->setCollection(collect($transformedProducts));

        // Return response
        return response()->json([
            'success' => true,
            'products' => $filteredProducts,
        ], Response::HTTP_OK);
    }

    // Fetch Single Product
    public function fetchSingleProduct($id)
    {
        // Fetch Single Product
        $product = $this->product::where('id', $id)->with(['category'])->firstOrFail();

        // Transform
        $transformerSingleProduct = fractal($product, new GuSingleProductTransformer());

        // Return response
        return response()->json([
            'success' => true,
            'product' => $transformerSingleProduct,
        ], Response::HTTP_OK);
    }

    // Fetch products by category
    public function fetchProductsByCategory()
    {

        // Init an empty array
        $categoryGroups = [];

        // Retrieve the list of category id's in use.
        $categories = $this->category->distinct()->select('id', 'name')->get();

        // For each category find the products associated and then add a collection of those products to the relevant array

        foreach ($categories as $category) {

            // Fetch category products
            $products = $this->product->where('category_id', $category->id)->get();

            // Transform
            $transformedProducts = fractal($products, new GuProductsTransformer)->parseIncludes('category');

            array_push($categoryGroups, ['category_name' => $category->name, 'products' => $transformedProducts]);
        }

        // Return response
        return response()->json([
            'success' => true,
            'categories' => $categoryGroups
        ]);
    }

    // Fetch Single category
    public function fetchSingleCategory($id)
    {
        // Fetch category along with products
        $category = $this->category->where('id', $id)->with(['products'])->first();

        // Transform
        $transformedCategory = fractal($category, new GuCategoryTransformer)->parseIncludes('products');

        // Return response
        return response()->json([
            'success' => true,
            'category' => $transformedCategory
        ]);
    }

    // Fetch top books
    public function fetchTopBooks()
    {

        $topBooks = $this->product
            ->select('products.*')
            ->leftJoin('purchase_items', function ($join) {
                $join->on('products.id', '=', 'purchase_items.entity_id')
                    ->where('purchase_items.entity_type', '=', PurchaseItemTypeData::PHYSICAL_PRODUCT);
            })
            ->selectRaw('COUNT(purchase_items.entity_id) as trending')
            ->groupBy('products.id')
            ->orderByDesc('trending')
            ->paginate(10);

        // Transform
        $transformedTopBooks = fractal($topBooks->getCollection(), new GuSingleProductTransformer());

        $topBooks->setCollection(collect($transformedTopBooks));

        // Return response
        return response()->json([
            'success' => true,
            'top-books' => $topBooks
        ]);
    }
}
