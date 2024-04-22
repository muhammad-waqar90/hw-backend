<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProgress extends Model
{
    use HasFactory;

    protected $guarded = [
        'id', 'created_at', 'updated_at'
    ];

    protected $hidden = [
        'id', 'created_at', 'updated_at'
    ];

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

    public function course()
    {
        return $this->belongsTo('App\Models\Course', 'entity_id', 'id');
    }
}
