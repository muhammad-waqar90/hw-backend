<?php

namespace App\Transformers\GU\Course;

use App\Models\Course;
use App\Traits\FileSystemsCloudTrait;
use App\Transformers\IU\IuCategoryTransformer;
use League\Fractal\TransformerAbstract;

class GuCourseAvailableListTransformer extends TransformerAbstract
{
    use FileSystemsCloudTrait;

    /**
     * List of resources to automatically include
     */
    protected array $defaultIncludes = [
        'category',
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
            'price' => $course->price,
            'status' => $course->status,
            'img' => $this->generateS3Link('courses/thumbnails/'.$course->img, 1),
        ];
    }

    public function includeCategory(Course $course)
    {
        return $this->item($course->category, new IuCategoryTransformer());
    }
}
