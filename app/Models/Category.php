<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];

    public function scopeMinimal($query)
    {
        return $query->select('id', 'name', 'parent_category_id', 'root_category_id');
    }

    public function firstChildCategories()
    {
        return $this->hasMany(Category::class, 'parent_category_id');
    }

    public function childCategoriesRecursive()
    {
        return $this->hasMany(Category::class)->with('childCategoriesRecursive');
    }

    public function parentCategory()
    {
        return $this->belongsTo(Category::class, 'parent_category_id');
    }

    public function parentCategoriesRecursive()
    {
        return $this->belongsTo(Category::class, 'parent_category_id')->with('parentCategoriesRecursive' . Category::minimalWithData());
    }

    public function rootParentCategory()
    {
        return $this->belongsTo(Category::class, 'root_category_id');
    }

    public static function minimalWithData()
    {
        return ':id,name,parent_category_id,root_category_id';
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
