<?php

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class FaqFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Faq::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faq_categories_id = DB::table('faq_categories')->pluck('id');
        return [
            'faq_category_id'   =>  $faq_categories_id->random(),
            'question'          =>  'question_number_' . fake()->unique()->randomDigit,
            'short_answer'      =>  'Short answer',
            'answer'            =>  'Longer answer than the shorter one',
            'published'         =>  0
        ];
    }
    public function withFaqCategoryId($id)
    {
        return $this->state(fn () => [
            'faq_category_id'   =>  $id,
        ]);
    }
    public function published()
    {
        return $this->state(fn () => [
            'published' =>  1,
        ]);
    }
}
