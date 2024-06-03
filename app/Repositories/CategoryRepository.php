<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository
{
    private Category $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    public function test()
    {
        return $this->category
            ->with('childCategoriesRecursive')
            ->with('parentCategoriesRecursive')
            ->with('rootParentCategory')
            ->get();
    }
}
