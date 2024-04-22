<?php

namespace App\Transformers\AF;

use App\Models\Course;
use App\Repositories\AF\AfCourseRepository;
use App\Traits\FileSystemsCloudTrait;
use League\Fractal\TransformerAbstract;

class AfCourseTransformer extends TransformerAbstract
{
    use FileSystemsCloudTrait;

    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        'category',
        'course_levels',
        'tier',
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
            'category_id' => $course->category_id,
            'description' => $course->description,
            'price' => $course->price,
            'course_levels_count' => $course->course_levels_count,
            'video_preview' => $course->video_preview,
            'status' => $course->status,
            'is_discounted' => (bool)$course->is_discounted,
            'img' => $this->generateS3Link(AfCourseRepository::getThumbnailS3StoragePath() . $course->img, 1)
        ];
    }

    public function includeCategory(Course $course)
    {
        return $this->item($course->category, new AfCategoryTransformer());
    }

    public function includeCourseLevels(Course $course)
    {
        return $this->collection($course->courseLevels, new AfCourseLevelTransformer());
    }

    public function includeTier(Course $course)
    {
        return $this->item($course->tier, new AfTierTransformer());
    }
}
