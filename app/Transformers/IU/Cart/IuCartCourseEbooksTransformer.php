<?php

namespace App\Transformers\IU\Cart;

use App\Models\Course;
use App\Repositories\AF\AfCourseRepository;
use App\Traits\FileSystemsCloudTrait;
use League\Fractal\TransformerAbstract;

class IuCartCourseEbooksTransformer extends TransformerAbstract
{
    use FileSystemsCloudTrait;

    /**
     * List of resources to automatically include
     */
    protected array $defaultIncludes = [
        'courseModules',
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Course $course)
    {
        return [
            'id' => $course->id,
            'name' => $course->name,
            'img' => $this->generateS3Link(AfCourseRepository::getThumbnailS3StoragePath().$course->img, 1),
            'price' => $course->price,
            'level_value' => $course->courseLevel->value,
            'level_name' => $course->courseLevel->name,
        ];
    }

    public function includeCourseModules(Course $course)
    {
        return $this->collection($course->courseLevel->courseModules, new IuCartEbookModulesTransformer());
    }
}
