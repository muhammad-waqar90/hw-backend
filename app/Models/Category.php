<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $guarded = [
        'id', 'created_at', 'updated_at'
    ];

    public function scopeMinimal($query)
    {
        return $query->select('id', 'name', 'parent_category_id', 'root_category_id');
    }

    public function firstChildCategories()
    {
        return $this->hasMany('App\Models\Category', 'parent_category_id');
    }

    public function childCategoriesRecursive()
    {
        return $this->hasMany('App\Models\Category')->with('childCategoriesRecursive');
    }

    public function parentCategory()
    {
        return $this->belongsTo('App\Models\Category', 'parent_category_id');
    }

    public function parentCategoriesRecursive()
    {
        return $this->belongsTo('App\Models\Category', 'parent_category_id')->with('parentCategoriesRecursive'.Category::minimalWithData());
    }

    public function rootParentCategory()
    {
        return $this->belongsTo('App\Models\Category', 'root_category_id');
    }

    public static function minimalWithData()
    {
        return ':id,name,parent_category_id,root_category_id';
    }

    public function products()
    {
        return $this->hasMany('App\Models\Product');
    }

    public function courses()
    {
        return $this->hasMany('App\Models\Course');
    }
}
