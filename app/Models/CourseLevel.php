<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseLevel extends Model
{
    use HasFactory;

    protected $guarded = [
        'id', 'created_at', 'updated_at'
    ];

    public function courseModules()
    {
        return $this->hasMany('App\Models\CourseModule');
    }

    public function course()
    {
        return $this->belongsTo('App\Models\Course', 'course_id');
    }
}
