<?php

namespace App\Transformers\AF\Product;

use App\Models\Product;
use App\Repositories\AF\AfProductRepository;
use App\Traits\FileSystemsCloudTrait;
use App\Transformers\AF\AfCategoryTransformer;
use League\Fractal\TransformerAbstract;

class AfProductTransformer extends TransformerAbstract
{
    use FileSystemsCloudTrait;

    /**
     * List of resources to automatically include
     */
    protected array $defaultIncludes = [
        'productMetas',
    ];

    /**
     * List of resources possible to include
     */
    protected array $availableIncludes = [
        'category',
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
            'course_module_id' => $product->course_module_id,
            'name' => $product->name,
            'description' => $product->description,
            'img' => $this->generateS3Link(AfProductRepository::getThumbnailS3StoragePath().$product->img, 1),
            'price' => $product->price,
            'is_available' => $product->is_available,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
        ];
    }

    public function includeProductMetas(Product $product)
    {
        return $this->collection($product->productMetas, new AfProductMetasTransformer());
    }

    public function includeCategory(Product $product)
    {
        return $this->item($product->category, new AfCategoryTransformer());
    }
}
