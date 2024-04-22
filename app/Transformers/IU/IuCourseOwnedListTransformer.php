<?php

namespace App\Transformers\IU;

use App\Models\Course;
use App\Repositories\IU\IuCourseRepository;
use App\Traits\FileSystemsCloudTrait;
use League\Fractal\TransformerAbstract;

class IuCourseOwnedListTransformer extends TransformerAbstract
{
    use FileSystemsCloudTrait;

    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        'category'
    ];

    /**
     * A Fractal transformer.
     *
     * @param Course $course
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
            'video_preview' => $course->video_preview ? $this->generateS3Link($course->video_preview, 1) : '',
            'price' => $course->price,
            'has_level_1_ebook' => $course->has_level_1_ebook,
            'status' => $course->status,
            'created_at' => $course->created_at,
            'updated_at' => $course->updated_at,
            'progress' => $course->progress
        ];
    }

    public function includeCategory(Course $course)
    {
        return $this->item($course->category, new IuCategoryTransformer());
    }
}
