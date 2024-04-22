<?php

namespace Database\Factories;

use App\Models\Ebook;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EbookFactory extends Factory
{
    /**
     * The name of the ebook's corresponding model.
     *
     * @var string
     */
    protected $model = Ebook::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $lesson_id = DB::table('lessons')->pluck('id');
        return [
            'lesson_id' =>  $lesson_id->random(),
            'content'   =>  Str::random(10),
        ];
    }

    public function withlessonId($id)
    {
        return $this->state(fn () => [
            'lesson_id' =>  $id,
        ]);
    }
}
