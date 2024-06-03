<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Tier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $tier = Tier::factory()->create();
        $categories_id = DB::table('categories')->pluck('id');

        return [
            'category_id' => $categories_id->random(),
            'name' => Str::random(10),
            'description' => fake()->sentence,
            'img' => fake()->imageUrl,
            'video_preview' => fake()->url,
            'price' => fake()->randomFloat($nbMaxDecimals = 2, $min = 10, $max = 800),
            'tier_id' => $tier->id,
        ];
    }

    public function withId($id)
    {
        return $this->state(fn () => [
            'category_id' => $id,
        ]);
    }

    public function withPrice($price)
    {
        return $this->state(fn () => [
            'price' => $price,
        ]);
    }

    public function withName($name)
    {
        return $this->state(fn () => [
            'name' => $name,
        ]);
    }

    public function withStatus($status)
    {
        return $this->state(fn () => [
            'status' => $status,
        ]);
    }

    public function withSalaryScaleDiscount()
    {
        return $this->state(fn () => [
            'is_discounted' => 1,
        ]);
    }
}
