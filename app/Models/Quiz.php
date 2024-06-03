<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];

    public function getQuestionsAttribute($value)
    {
        return json_decode($value);
    }

    public function getAnswersAttribute($value)
    {
        return json_decode($value);
    }

    public function getHardQuestionsAttribute($value)
    {
        return json_decode($value);
    }

    public function getHardAnswersAttribute($value)
    {
        return json_decode($value);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'entity_id', 'id');
    }

    public function courseModule()
    {
        return $this->belongsTo(CourseModule::class, 'entity_id', 'id');
    }

    public function courseLevel()
    {
        return $this->belongsTo(CourseLevel::class, 'entity_id', 'id');
    }

    public function quizItems()
    {
        return $this->hasMany(QuizItem::class);
    }
}
