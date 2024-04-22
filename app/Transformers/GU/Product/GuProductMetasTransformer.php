<?php

namespace App\Transformers\GU\Product;

use App\Models\ProductMeta;
use League\Fractal\TransformerAbstract;

class GuProductMetasTransformer extends TransformerAbstract
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
