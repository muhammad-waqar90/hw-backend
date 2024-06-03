<?php

namespace App\Transformers\IU;

use App\Models\Category;
use League\Fractal\TransformerAbstract;

class IuCategoryTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Category $category)
    {
        return [
            'id' => $category->id,
            'parent_category_id' => $category->parent_category_id,
            'root_category_id' => $category->root_category_id,
            'name' => $category->name,
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
        ];
    }
}
