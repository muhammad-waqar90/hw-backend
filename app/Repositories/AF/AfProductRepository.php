<?php

namespace App\Repositories\AF;

use App\DataObject\Purchases\PurchaseItemTypeData;
use App\Models\Product;
use App\Models\ProductMeta;
use App\Traits\FileSystemsCloudTrait;
use Illuminate\Support\Carbon;

class AfProductRepository
{
    use FileSystemsCloudTrait;

    private Product $product;
    private ProductMeta $productMeta;

    public function __construct(Product $product, ProductMeta $productMeta)
    {
        $this->product = $product;
        $this->productMeta = $productMeta;
    }

    public static function getThumbnailS3StoragePath()
    {
        return 'products/thumbnails/';
    }

    public function createProduct($categoryId, $name, $description, $thumbnail, $price)
    {
        return $this->product->create([
            'category_id'   => $categoryId,
            'name'          => $name,
            'description'   => $description,
            'img'           => $thumbnail,
            'price'         => $price,
            'is_available'  => 1, // TODO: product availability should be dynamic
            'type'          => PurchaseItemTypeData::PHYSICAL_PRODUCT
        ]);
    }

    public function prepareProductMetaData($productId, $productMetas)
    {
        $productMetaData = [];
        foreach ($productMetas as $metaKey => $metaValue) {
            $productMeta = [
                'product_id'    => $productId,
                'meta_key'      => $metaKey,
                'meta_value'    => $metaValue,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now()
            ];

            array_push($productMetaData, $productMeta);
        }

        return $productMetaData;
    }

    public function insertProductMetas($productMetas)
    {
        return $this->productMeta->insert($productMetas);
    }

    public function getProductListQuery($searchText = null, $categoryId = null, $isAvailable = null, $isNotBounded = null)
    {
        return $this->product
            ->when($searchText, function ($query) use ($searchText) {
                $query->where('name', 'LIKE', "%$searchText%");
            })
            ->when($isAvailable, function ($query) use ($isAvailable) {
                $query->where('is_available', $isAvailable);
            })
            ->when($isNotBounded, function ($query) {
                $query->whereNull('course_module_id');
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            });
    }

    public function getProductQuery($id)
    {
        return $this->product
            ->where('id', $id);
    }

    public function getProductDetailed($id)
    {
        return $this->getProductQuery($id)
            ->with('category')
            ->with('productMetas')
            ->first();
    }

    public function getProduct($id)
    {
        return $this->getProductQuery($id)->first();
    }

    public function deleteProduct($id)
    {
        return $this->product->where('id', $id)->delete();
    }

    public function updateProduct($id, $categoryId, $name, $description, $thumbnail, $price, $isAvailable)
    {
        return $this->product->where('id', $id)->update([
            'category_id'   => $categoryId,
            'name'          => $name,
            'description'   => $description,
            'img'           => $thumbnail,
            'price'         => $price,
            'is_available'  => $isAvailable,
        ]);
    }

    public function updateAuthor($product, $author)
    {
        $productMetas = $product->productMetas;
        foreach ($productMetas as $productMeta) :
            if ($productMeta['meta_key'] === 'author') :
                $productMeta['meta_value'] = $author;
                $productMeta->save();
            endif;
        endforeach;
    }

    public function bindPhysicalBookWithCourseModule($bookId, $courseModuleId)
    {
        return $this->product
            ->where('id', $bookId)
            ->update(['course_module_id' => $courseModuleId]);
    }

    public function unbindPhysicalBookWithCourseModule($courseModuleIds)
    {
        return $this->product
            ->whereIn('course_module_id', $courseModuleIds)
            ->update(['course_module_id' => null]);
    }

    public function getBookBoundedWithModule($courseModuleIds)
    {
        return $this->product
            ->whereIn('course_module_id', $courseModuleIds)
            ->get();
    }
}
