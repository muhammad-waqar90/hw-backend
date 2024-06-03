<?php

namespace App\Models;

use App\DataObject\QuizData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
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

    public function courseModule()
    {
        return $this->belongsTo(CourseModule::class);
    }

    public function lessonFaqs()
    {
        return $this->hasMany(LessonFaq::class);
    }

    public function publishLesson()
    {
        return $this->hasOne(PublishLesson::class);
    }

    public function quiz()
    {
        return $this->hasOne(Quiz::class, 'entity_id', 'id')->where('entity_type', QuizData::ENTITY_LESSON);
    }
}
