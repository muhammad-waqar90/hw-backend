<?php

namespace App\Transformers\GU\Product;

use App\Models\Product;
use App\Traits\FileSystemsCloudTrait;
use App\Transformers\GU\GuCategoryTransformer;
use League\Fractal\TransformerAbstract;

class GuSingleProductTransformer extends TransformerAbstract
{
    use FileSystemsCloudTrait;

    /**
     * List of resources to automatically include
     */
    protected array $defaultIncludes = [
        'category',
        'productMetas',
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Product $product)
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'img' => $this->generateS3Link('products/thumbnails/'.$product->img, 1),
            'meta' => $product->meta,
            'price' => $product->price,
            'is_available' => $product->is_available,
            'type' => $product->type,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
        ];
    }

    public function includeCategory(Product $product)
    {
        return $this->item($product->category, new GuCategoryTransformer());
    }

    public function includeProductMetas(Product $product)
    {
        return $this->collection($product->productMetas, new GuProductMetasTransformer());
    }
}
