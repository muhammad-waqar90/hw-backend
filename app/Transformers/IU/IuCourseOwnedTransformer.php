<?php

namespace App\Transformers\IU;

use App\Models\Course;
use App\Repositories\IU\IuCourseRepository;
use App\Traits\FileSystemsCloudTrait;
use League\Fractal\TransformerAbstract;

class IuCourseOwnedTransformer extends TransformerAbstract
{
    use FileSystemsCloudTrait;

    /**
     * List of resources to automatically include
     */
    protected array $defaultIncludes = [
        'course_level',
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
            'category_id' => $course->category_id,
            'name' => $course->name,
            'description' => $course->description,
            'img' => $this->generateS3Link(IuCourseRepository::getCourseThumbnailS3StoragePath().$course->img, 1),
            'video_preview' => $course->video_preview,
            'created_at' => $course->created_at,
            'updated_at' => $course->updated_at,
            'progress' => $course->progress ? $course->progress : 0,
            'category_with_recursive_parents' => $course->categoryWithRecursiveParents,
            'has_level_1_ebook' => $course->has_level_1_ebook,
        ];
    }

    public function includeCourseLevel(Course $course)
    {
        return $this->item($course->courseLevel, new IuCourseLevelTransformer(true));
    }

    public function includeCourseLevels(Course $course)
    {
        return $this->collection($course->courseLevels, new IuCourseLevelListTransformer());
    }
}
