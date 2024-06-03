<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseLevel extends Model
{
    use HasFactory;

    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];

    public function courseModules()
    {
        return $this->hasMany(CourseModule::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
