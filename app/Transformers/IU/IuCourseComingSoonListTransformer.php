<?php

namespace App\Transformers\IU;

use App\Models\Course;
use App\Repositories\IU\IuCourseRepository;
use App\Traits\FileSystemsCloudTrait;
use League\Fractal\TransformerAbstract;

class IuCourseComingSoonListTransformer extends TransformerAbstract
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
            'img'           => $this->generateS3Link(IuCourseRepository::getCourseThumbnailS3StoragePath().$course->img, 1),
        ];
    }
}
