<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Course extends Model
{
    use HasFactory;

    protected $guarded = [
        'id', 'created_at', 'updated_at'
    ];

    public function category()
    {
        return $this->belongsTo('App\Models\Category', 'category_id');
    }

    public function categoryWithRecursiveParents()
    {
        return $this->belongsTo('App\Models\Category', 'category_id')->with('parentCategoriesRecursive' . Category::minimalWithData());
    }

    public function courseLevels()
    {
        return $this->hasMany('App\Models\CourseLevel');
    }

    public function courseLevel()
    {
        return $this->hasOne('App\Models\CourseLevel');
    }

    public function courseModules()
    {
        return $this->hasMany('App\Models\CourseModule');
    }

    public function courseModuleWithLessons()
    {
        return $this->hasMany('App\Models\CourseModule')->with('lessons' . CourseModule::minimalLessonsWithData());
    }

    public function purchaseItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\PurchaseItem');
    }

    public function tier()
    {
        return $this->belongsTo('App\Models\Tier', 'tier_id');
    }

    public function isSalaryScaleDiscountEnabled($courseId)
    {
        return $this->where('id', $courseId)->pluck('is_discounted')[0];
    }
}
