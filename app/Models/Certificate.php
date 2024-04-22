<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $guarded = [
        'id', 'created_at', 'updated_at'
    ];

    /**
     * Get the parent entity model.
        * Course
        * CourseLevel
        * CourseModule
     */
    public function entity()
    {
        return $this->morphTo();
    }
}
