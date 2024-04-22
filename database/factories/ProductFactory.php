<?php

namespace Database\Factories;

use App\DataObject\Purchases\PurchaseItemTypeData;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $categories_id = DB::table('categories')->pluck('id');
        return [
            'category_id'       =>  $categories_id->random(),
            'course_module_id'  =>  null,
            'name'              =>  Str::random(10),
            'description'       =>  fake()->sentence,
            'img'               =>  fake()->imageUrl,
            'price'             =>  fake()->randomFloat($nbMaxDecimals = 2, $min = 10, $max = 800),
            'is_available'      =>  1,
            'type'              =>  PurchaseItemTypeData::PHYSICAL_PRODUCT
        ];
    }

    public function withCategoryId($category_id)
    {
        return $this->state(fn () => [
            'category_id'   =>  $category_id
        ]);
    }
}
