<?php

namespace Database\Factories;

use App\Models\FaqCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class FaqCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FaqCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'faq_category_id'   =>  null,
            'name'              =>  'faq_category_' . fake()->unique()->randomNumber,
            'published'         =>  0
        ];
    }
    public function withFaqCategoryId($id)
    {
        return $this->state(fn () => [
            'faq_category_id'   =>  $id,
        ]);
    }
    public function withName($name)
    {
        return $this->state(fn () => [
            'name'  =>  $name,
        ]);
    }
    public function published()
    {
        return $this->state(fn () => [
            'published' =>  1,
        ]);
    }
}
