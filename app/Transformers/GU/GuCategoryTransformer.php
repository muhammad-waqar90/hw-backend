<?php

namespace App\Transformers\GU;

use App\Models\Category;
use App\Transformers\GU\Product\GuProductsTransformer;
use League\Fractal\TransformerAbstract;

class GuCategoryTransformer extends TransformerAbstract
{
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        'products',
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Category $category)
    {
        return [
            'id'                    =>  $category->id,
            'parent_category_id'    =>  $category->parent_category_id,
            'root_category_id'      =>  $category->root_category_id,
            'name'                  =>  $category->name,
            'created_at'            =>  $category->created_at,
            'updated_at'            =>  $category->updated_at
        ];
    }

    public function includeProducts(Category $category)
    {
        return $this->collection($category->products, new GuProductsTransformer());
    }
}
