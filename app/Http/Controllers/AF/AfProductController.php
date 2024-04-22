<?php

namespace App\Http\Controllers\AF;

use App\Http\Controllers\Controller;
use App\Http\Requests\AF\Product\AfProductListRequest;
use App\Http\Requests\AF\Product\AfProductCreateRequest;
use App\Http\Requests\AF\Product\AfProductUpdateRequest;
use App\Repositories\AF\AfProductRepository;
use App\Traits\FileSystemsCloudTrait;
use App\Transformers\AF\Product\AfProductTransformer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class AfProductController extends Controller
{
    use FileSystemsCloudTrait;

    private AfProductRepository $afProductRepository;

    public function __construct(AfProductRepository $afProductRepository)
    {
        $this->afProductRepository = $afProductRepository;
    }

    public function createProduct(AfProductCreateRequest $request)
    {
        $thumbnail = $this->uploadFile($this->afProductRepository->getThumbnailS3StoragePath(), $request->img);

        DB::beginTransaction();
        try {
            $product = $this->afProductRepository->createProduct(
                $request->category_id,
                $request->name,
                $request->description,
                $thumbnail,
                $request->price
            );

            if($request->product_metas) {
                $productMetas = $this->afProductRepository->prepareProductMetaData($product->id, $request->product_metas);
                $this->afProductRepository->insertProductMetas($productMetas);
            }

            DB::commit();

            return response()->json(['message' => Lang::get('general.successfullyCreated', ['model' => 'product'])], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Exception: AfProductController@createProduct', [$e->getMessage()]);
            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function getProductList(AfProductListRequest $request)
    {
        $data = $this->afProductRepository
            ->getProductListQuery($request->searchText)
            ->latest()
            ->paginate(20)
            ->appends([
                'searchText' => $request->searchText
            ]);

        $fractal = fractal($data->getCollection(), new AfProductTransformer());
        $data->setCollection(collect($fractal));

        return response()->json($data, 200);
    }

    public function getProduct(int $id)
    {
        $product = $this->afProductRepository->getProductDetailed($id);
        if (!$product)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $fractal = fractal($product, new AfProductTransformer)->parseIncludes('category');
        return response()->json($fractal, 200);
    }

    public function deleteProduct(int $id)
    {
        $product = $this->afProductRepository->getProduct($id);
        if (!$product)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $this->afProductRepository->deleteProduct($id);

        return response()->json(['message' => Lang::get('general.successfullyDeleted', ['model' => 'product'])], 200);
    }

    public function updateProduct(int $id, AfProductUpdateRequest $request)
    {
        $product = $this->afProductRepository->getProduct($id);
        if (!$product)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $thumbnail = $request->img ? $this->updateFile($this->afProductRepository->getThumbnailS3StoragePath(), $product->img, $request->img) : $product->img;

        DB::beginTransaction();
        try {
            $this->afProductRepository->updateProduct(
                $id,
                $request->category_id,
                $request->name,
                $request->description,
                $thumbnail,
                $request->price,
                $request->is_available,
            );

            if ($request->author)
                $this->afProductRepository->updateAuthor($product, $request->author);

            DB::commit();

            return response()->json(['message' => Lang::get('general.successfullyUpdated', ['model' => 'product'])], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Exception: AfProductController@updateProduct', [$e->getMessage()]);
            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function getAllUnboundedBooks(AfProductListRequest $request)
    {
        $isAvailable = 1;
        $isNotBounded = true;

        $data = $this->afProductRepository
            ->getProductListQuery($request->searchText, null, $isAvailable, $isNotBounded)
            ->orderBy('name', 'ASC')
            ->limit(10)
            ->get();

        return response()->json($data, 200);
    }
}
