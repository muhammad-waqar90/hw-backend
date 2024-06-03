<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProgress extends Model
{
    use HasFactory;

    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];

    protected $hidden = [
        'id', 'created_at', 'updated_at',
    ];

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

    public function course()
    {
        return $this->belongsTo(Course::class, 'entity_id', 'id');
    }
}
