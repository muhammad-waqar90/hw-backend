<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */

    public function definition()
    {
        return [
            'parent_category_id'    =>  null,
            'root_category_id'      =>  null,
            'name'                  =>  Str::random(10),
        ];
    }

    public function childOf($category)
    {
        if ($category->root_category_id == null) {
            return $this->state(fn () => [
                'parent_category_id'    =>  $category->id,
                'root_category_id'      =>  $category->id,
            ]);
        } else if ($category->root_category_id != null) {
            return $this->state(fn () => [
                'parent_category_id'    =>  $category->id,
                'root_category_id'      =>  $category->root_category_id,
            ]);
        }
    }
    public function withCategoryId()
    {
        $categories = DB::table('categories')->pluck('id');
        if ($categories->isEmpty()) {
            return $this->state(fn ()  =>  []);
        } else {
            return $this->state(fn () => [
                'parent_category_id'    =>  $categories->random(),
            ]);
        }
    }
    public function withRootCategoryId()
    {
        $categories = DB::table('categories')->whereNull('parent_category_id')->whereNull('root_category_id')->get();
        if ($categories->isEmpty()) {
            return $this->state(fn ()  =>  []);
        } else {
            return $this->state(fn () => [
                'parent_category_id'    =>  $categories->pluck('id')->random(),
                'root_category_id'      =>  $categories->where('id', $categories->pluck('id')->random())->pluck('parent_category_id')->first(),
            ]);
        }
    }

    public function withName($name)
    {
        return $this->state(function (array $attributes) use ($name) {
            return [
                'name'  => $name
            ];
        });
    }

    /*
    public function configure()
    {
        return $this->afterCreating(function (Category $category) {
            $category->category_id = $category->id;
        });
    }   */
}
