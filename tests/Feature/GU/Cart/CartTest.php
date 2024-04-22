<?php

namespace Tests\Feature\GU\Cart;

use App\DataObject\Purchases\PurchaseItemTypeData;
use App\Models\GuestCart;
use App\Models\Product;
use App\Traits\Tests\CourseTestTrait;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Tests\TestCase;

class CartTest extends TestCase
{
    use CourseTestTrait;
    private $data;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->data = $this->CategoryCourseCourseModuleLessonSeeder();
    }


    public function testCreateGuestCartPostRoute()
    {
        $response = $this->json('POST',  '/api/gu/guest-carts/create', [
            'cart_id'   => Str::random(5),
            'items'     => [[
                'id'    => $this->data->course->id,
                'type'  => PurchaseItemTypeData::COURSE
            ]]
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('message', Lang::get('general.successfullyCreated', ['model' => 'guest cart']));
    }

    public function testGetGuestCartByIdGetRoute()
    {
        $cartId = Str::random(5);
        GuestCart::factory()->withCartId($cartId)->create();

        $response = $this->json('GET',  '/api/gu/guest-carts/guest-cart/' . $cartId);

        $response->assertStatus(200);
        $response->assertJsonPath('guest-cart.cart_id', $cartId);
    }

    public function testDeleteGuestCartByIdDeleteRoute()
    {
        $cartId = Str::random(5);
        GuestCart::factory()->withCartId($cartId)->create();

        $response = $this->json('DELETE',  '/api/gu/guest-carts/' . $cartId);

        $response->assertStatus(200);
        $response->assertJsonPath('message', Lang::get('general.successfullyDeleted', ['model' => 'guest cart']));
    }

    public function testMapCartItemsGetRoute()
    {
        $product = Product::factory()->create();
        $items = [
            [
                'id'    => $this->data->course->id,
                'type'  => PurchaseItemTypeData::COURSE
            ],
            [
                'id'    => $product->id,
                'type'  => PurchaseItemTypeData::PHYSICAL_PRODUCT
            ]
        ];

        $response = $this->json('POST',  '/api/gu/guest-carts/map-cart-items', [
            'items' => $items
        ]);

        $response->assertStatus(200);
        $this->assertEquals(count($response['products']), count($items));
    }
}
