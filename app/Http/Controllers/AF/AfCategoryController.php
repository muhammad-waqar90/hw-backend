<?php

namespace App\Http\Controllers\AF;

use App\Http\Controllers\Controller;
use App\Http\Requests\AF\Categories\AfCategoryCreateUpdateRequest;
use App\Http\Requests\AF\Categories\AfCategorySearchRequest;
use App\Repositories\AF\AfCategoryRepository;
use App\Transformers\AF\AfCategoryTransformer;
use Illuminate\Support\Facades\Lang;

class AfCategoryController extends Controller
{
    private AfCategoryRepository $afCategoryRepository;

    public function __construct(AfCategoryRepository $afCategoryRepository)
    {
        $this->afCategoryRepository = $afCategoryRepository;
    }

    public function getCategoryListDetailed(AfCategorySearchRequest $request)
    {
        $data = $this->afCategoryRepository->getCategoryListQuery($request->searchText)
            ->orderBy('id', 'ASC')
            ->with('parentCategoriesRecursive')
            ->paginate(20)
            ->appends(['searchText'  => $request->searchText]);

        $fractal = fractal($data->getCollection(), new AfCategoryTransformer());
        $data->setCollection(collect($fractal));

        return response()->json($data, 200);
    }

    public function getRootCategoryList()
    {
        $data = $this->afCategoryRepository->getRootCategoryList();
        return response()->json($data, 200);
    }

    public function createCategory(AfCategoryCreateUpdateRequest $request)
    {
        $requestValidations = $this->createUpdateRequestValidation($request);
        if($requestValidations['errors'])
            return response()->json(['errors' => $requestValidations['errors']], $requestValidations['errorCode']);

        $this->afCategoryRepository->createCategory($request->parent_category_id, $request->root_category_id, $request->name);
        return response()->json(['message' => 'Successfully created category'], 200);
    }

    public function getCategory(int $id)
    {
        $category = $this->afCategoryRepository->getCategory($id);
        if(!$category)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        return response()->json($category, 200);
    }

    public function updateCategory(AfCategoryCreateUpdateRequest $request, int $id)
    {
        $category = $this->afCategoryRepository->getCategory($id);
        if(!$category)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        if($id === (int) $request->parent_category_id || $id === (int) $request->root_category_id)
            return response()->json(['errors' => 'Invalid parent/root category'], 400);
        if(!$this->canUpdateCategory($category, $request))
            return response()->json(['errors' => 'Cannot update parent/root category for a category that has child categories attached to it'], 400);

        $requestValidations = $this->createUpdateRequestValidation($request);
        if($requestValidations['errors'])
            return response()->json(['errors' => $requestValidations['errors']], $requestValidations['errorCode']);

        $this->afCategoryRepository->updateCategory($id, $request->parent_category_id, $request->root_category_id, $request->name);
        return response()->json(['message' => 'Successfully updated category'], 200);
    }

    public function deleteCategory(int $id)
    {
        $category = $this->afCategoryRepository->getCategory($id);
        if(!$category)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);

        $hasCategoryChild = $this->afCategoryRepository->hasCategoryChild($id);
        $isCategoryUsed = $this->afCategoryRepository->isCategoryUsed($id);
        if($hasCategoryChild || $isCategoryUsed)
            return response()->json(['errors' => 'This category cannot be deleted due to having child categories/courses/products'], 400);

        $category->delete();
        return response()->json(['message' => 'Successfully deleted category'], 200);
    }

    public function getChildCategoriesForRootCategory(int $id)
    {
        $data = $this->afCategoryRepository->getChildCategoriesForRootCategory($id);
        return response()->json($data, 200);
    }

    public function getCategoryList(AfCategorySearchRequest $request)
    {
        $data = $this->afCategoryRepository
            ->getCategoryListQuery($request->searchText)
            ->orderBy('name', 'ASC')
            ->limit(10)
            ->get();

        return response()->json($data, 200);
    }

    private function createUpdateRequestValidation(AfCategoryCreateUpdateRequest $request): array
    {
        if(!$request->root_category_id)
            return ['errors' => ''];

        $rootCategory = $this->afCategoryRepository->getCategory($request->root_category_id);
        if(!$rootCategory)
            return ['errors' => 'Root category does not exist', 'errorCode' => 404];

        $parentCategory = $this->afCategoryRepository->getCategory($request->parent_category_id);
        if(!$parentCategory)
            return['errors' => 'Parent category does not exist', 'errorCode' => 404];

        if($parentCategory->root_category_id !== (int) $request->root_category_id && $parentCategory->id !== (int) $request->root_category_id )
            return ['errors' => 'Mismatch between parent and root categories', 'errorCode' => 400];

        return ['errors' => ''];
    }

    private function canUpdateCategory($category, AfCategoryCreateUpdateRequest $request)
    {
        return $category->firstChildCategories->isEmpty() ||
            ($category->parent_category_id === (int) $request->parent_category_id &&
                $category->root_category_id === (int) $request->root_category_id);
    }
}
