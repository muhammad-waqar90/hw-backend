<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserQuiz extends Model
{
    use HasFactory;

    protected $guarded = [
        'id', 'created_at', 'updated_at'
    ];

    public function getQuestionsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getAnswersAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getUserAnswersAttribute($value)
    {
        return json_decode($value, true);
    }

    public function lesson()
    {
        return $this->belongsTo('App\Models\Lesson', 'entity_id', 'id');
    }

    public function courseModule()
    {
        return $this->belongsTo('App\Models\CourseModule', 'entity_id', 'id');
    }

    public function courseLevel()
    {
        return $this->belongsTo('App\Models\CourseLevel', 'entity_id', 'id');
    }
}
