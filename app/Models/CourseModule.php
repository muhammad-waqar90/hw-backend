<?php

namespace App\Models;

use App\Models\Product as Book;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseModule extends Model
{
    use HasFactory;

    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('order', function ($query) {
            $query->orderBy('order_id', 'asc');
        });
    }

    public function course()
    {
        return $this->belongsTo('App\Models\Course', 'course_id');
    }

    public function courseLevel()
    {
        return $this->belongsTo('App\Models\CourseLevel');
    }

    public function lessons()
    {
        return $this->hasMany('App\Models\Lesson');
    }

    public function ebook()
    {
        return $this->hasOne('App\Models\Lesson');
    }

    public function quiz()
    {
        return $this->morphMany('App\Models\Quiz', 'entity');
    }

    public static function minimalLessonsWithData()
    {
        return ':id,name,course_module_id';
    }

    public function book()
    {
        return $this->hasOne(Book::class);
    }

}
