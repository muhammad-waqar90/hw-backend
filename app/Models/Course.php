<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function categoryWithRecursiveParents()
    {
        return $this->belongsTo(Category::class, 'category_id')->with('parentCategoriesRecursive' . Category::minimalWithData());
    }

    public function courseLevels()
    {
        return $this->hasMany(CourseLevel::class);
    }

    public function courseLevel()
    {
        return $this->hasOne(CourseLevel::class);
    }

    public function courseModules()
    {
        return $this->hasMany(CourseModule::class);
    }

    public function courseModuleWithLessons()
    {
        return $this->hasMany(CourseModule::class)->with('lessons' . CourseModule::minimalLessonsWithData());
    }

    public function purchaseItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function tier()
    {
        return $this->belongsTo(Tier::class, 'tier_id');
    }

    public function isSalaryScaleDiscountEnabled($courseId)
    {
        return $this->where('id', $courseId)->pluck('is_discounted')[0];
    }
}
