<?php

namespace App\Transformers\AF;

use App\Models\Category;
use League\Fractal\TransformerAbstract;

class AfCategoryTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     * @param Category $category
     * @return array
     */
    public function transform(Category $category)
    {
        return [
            'id'                           => $category->id,
            'parent_category_id'           => $category->parent_category_id,
            'root_category_id'             => $category->root_category_id,
            'name'                         => $category->name,
            'parent_categories_recursive'  => $category->parentCategoriesRecursive,
        ];
    }
}
