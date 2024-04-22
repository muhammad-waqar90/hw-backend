<?php

namespace App\Models;

use App\DataObject\QuizData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $guarded = [
        'id', 'created_at', 'updated_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('order', function ($query) {
            $query->orderBy('order_id', 'asc');
        });
    }

    public function courseModule()
    {
        return $this->belongsTo('App\Models\CourseModule');
    }

    public function lessonFaqs()
    {
        return $this->hasMany('App\Models\LessonFaq');
    }

    public function publishLesson()
    {
        return $this->hasOne('App\Models\PublishLesson');
    }

    public function quiz()
    {
        return $this->hasOne('App\Models\Quiz', 'entity_id', 'id')->where('entity_type', QuizData::ENTITY_LESSON);
    }
}
