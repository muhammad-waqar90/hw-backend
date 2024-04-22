<?php

namespace App\Transformers\AF\Product;

use App\Models\ProductMeta;
use League\Fractal\TransformerAbstract;

class AfProductMetasTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(ProductMeta $productMeta)
    {
        return [
            'id' => $productMeta->id,
            'product_id' => $productMeta->product_id,
            'meta_key' => $productMeta->meta_key,
            'meta_value' => $productMeta->meta_value,
            'created_at' => $productMeta->created_at,
            'updated_at' => $productMeta->updated_at,
        ];
    }
}
