<?php

namespace App\Repositories\GU;

use App\DataObject\GuestData;
use App\Models\Course;
use App\Models\GuestCart;
use App\Models\Product;
use App\Transformers\GU\Course\GuCourseTransformer;
use App\Transformers\GU\Product\GuSingleProductTransformer;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Lang;

class GuCartRepository
{
    // GuestCart Model
    private GuestCart $guestCart;

    // Course Model
    private Course $course;

    // Product Model
    private Product $product;

    public function __construct(GuestCart $guestCart, Course $course, Product $product)
    {
        // Inject Model
        $this->guestCart = $guestCart;
        $this->course = $course;
        $this->product = $product;
    }

    // Create Guest Cart
    public function createGuestCart($request)
    {
        $this->guestCart->cart_id = $request->cart_id;
        $this->guestCart->items = $request->items;
        $this->guestCart->is_completed = GuestData::GUEST_CART_INCOMPLETE;

        // Save Cart
        $this->guestCart->save();

        // Return response
        return response()->json([
            'success' => true,
            'message' => Lang::get('general.successfullyCreated', ['model' => 'guest cart'])
        ], Response::HTTP_CREATED);
    }

    // Fetch Guest Cart
    public function fetchGuestCart($cartId)
    {
        // Fetch
        $guestCart = $this->guestCart->where('cart_id', $cartId)->first();

        // Return response
        return response()->json([
            'success' => true,
            'guest-cart' => $guestCart
        ], Response::HTTP_OK);
    }


    // Delete Guest Cart
    public function deleteGuestCart($cartId)
    {
        // Fetch
        $guestCart = $this->guestCart->where('cart_id', $cartId)->first();

        if (is_null($guestCart)) {
            throw new Exception(Lang::get('general.notFound'), Response::HTTP_NOT_FOUND);
        }

        // Delete
        $guestCart->delete();

        // Return response
        return response()->json([
            'success' => true,
            'message' => Lang::get('general.successfullyDeleted', ['model' => 'guest cart'])
        ]);
    }

    // Fetch combined products (physical books & courses);
    public function fetchCombinedProducts($request)
    {
        // Initiate collection
        $products = [];

        // Items
        $items = $request->items;

        // Iterate through $items
        foreach ($items as $item) :

            if ($item['type'] === 'course') :
                $course = $this->course->where('id', $item['id'])->first();

                // Transform
                $courseFractal = fractal($course, new GuCourseTransformer());
                array_push($products, $courseFractal);
            endif;

            if ($item['type'] === 'physical_product') :
                $physicalProduct = $this->product->where('id', $item['id'])->first();
                $physicalProductFractal = fractal($physicalProduct, new GuSingleProductTransformer());
                array_push($products, $physicalProductFractal);
            endif;
        endforeach;

        // Return response
        return response()->json([
            'success' => true,
            'products' => $products
        ], Response::HTTP_OK);
    }
}
