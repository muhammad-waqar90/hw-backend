<?php

namespace App\Transformers\GU\Course;

use App\Models\Course;
use App\Traits\FileSystemsCloudTrait;
use League\Fractal\TransformerAbstract;

class GuCourseComingSoonListTransformer extends TransformerAbstract
{
    use FileSystemsCloudTrait;
    
    /**
     * A Fractal transformer.
     * 
     * @param Course $course
     * @return array
     */
    public function transform(Course $course)
    {
        return [
            'id'            => $course->id,
            'name'          => $course->name,
            'description'   => $course->description,
            'img'           => $this->generateS3Link('courses/thumbnails/' . $course->img, 1)
        ];
    }
}
