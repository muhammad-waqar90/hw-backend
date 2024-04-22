<?php

namespace App\Repositories\AF;

use App\Models\Category;

class AfCategoryRepository
{
    private Category $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    public function getCategoryListQuery($searchText = null)
    {
        return $this->category
            ->when($searchText, function ($query) use ($searchText) {
                $query->where('name', 'LIKE', "%$searchText%");
            });
    }

    public function getRootCategoryList()
    {
        return $this->category
            ->whereNull('parent_category_id')
            ->whereNull('root_category_id')
            ->get();
    }

    public function getCategory($id)
    {
        return $this->category->where('id', $id)->first();
    }

    public function createCategory($parentCategoryId, $rootCategoryId, $name)
    {
        return $this->category->create([
            'parent_category_id' => $parentCategoryId,
            'root_category_id'   => $rootCategoryId,
            'name'               => $name
        ]);
    }

    public function updateCategory($id, $parentCategoryId, $rootCategoryId, $name)
    {
        return $this->category->where('id', $id)->update([
            'parent_category_id' => $parentCategoryId,
            'root_category_id'   => $rootCategoryId,
            'name'               => $name
        ]);
    }

    public function getChildCategoriesForRootCategory($id)
    {
        return $this->category->where('root_category_id', $id)
            ->with('parentCategoriesRecursive')
            ->get();
    }

    public function hasCategoryChild($id)
    {
        return $this->category
            ->where('parent_category_id', $id)
            ->orWhere('root_category_id', $id)
            ->first();
    }

    public function isCategoryUsed($id)
    {
        return $this->category
            ->where('id', $id)
            ->where(function ($query) {
                $query->whereHas('courses')
                    ->orWhereHas('products');
            })
            ->exists();
    }
}
