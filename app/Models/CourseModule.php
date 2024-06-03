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
            $query->oldest('order_id');
        });
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function courseLevel()
    {
        return $this->belongsTo(CourseLevel::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function ebook()
    {
        return $this->hasOne(Lesson::class);
    }

    public function quiz()
    {
        return $this->morphMany(Quiz::class, 'entity');
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
