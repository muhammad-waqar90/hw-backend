<?php

namespace App\Transformers\GU\Course;

use App\Models\Course;
use App\Traits\FileSystemsCloudTrait;
use App\Transformers\GU\GuCourseLevelTransformer;
use League\Fractal\TransformerAbstract;

class GuCourseTransformer extends TransformerAbstract
{
    use FileSystemsCloudTrait;

    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        'course_levels',
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
            'img' => $this->generateS3Link('courses/thumbnails/' . $course->img, 1),
            'type' => 'course'
        ];
    }

    public function includeCourseLevels(Course $course)
    {
        return $this->collection($course->courseLevels, new GuCourseLevelTransformer());
    }
}
