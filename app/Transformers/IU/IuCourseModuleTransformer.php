<?php

namespace App\Transformers\IU;

use App\Models\CourseModule;
use App\Repositories\IU\IuCourseRepository;
use App\Traits\FileSystemsCloudTrait;
use League\Fractal\TransformerAbstract;

class IuCourseModuleTransformer extends TransformerAbstract
{
    use FileSystemsCloudTrait;

    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        'lessons'
    ];

    protected $passedPreviousLevel;

    public function __construct($passedPreviousLevel)
    {
        $this->passedPreviousLevel = $passedPreviousLevel;
    }

    /**
     * A Fractal transformer.
     *
     * @param CourseModule $courseModule
     * @return array
     */
    public function transform(CourseModule $courseModule)
    {
        return [
            'id'    => $courseModule->id,
            'name'  => $courseModule->name,
            'description'   => $courseModule->description,
            'img' => $courseModule->img ? $this->generateS3Link(IuCourseRepository::getCourseModuleThumbnailS3StoragePath().$courseModule->img , 1) : null,
            'video_preview' => $courseModule->video_preview ? $this->generateS3Link($courseModule->video_preview, 1) : '',
            'order_id'  => $courseModule->order_id,
            'progress'  => $courseModule->progress ?: 0,
            'has_quiz' => !!$courseModule->quiz_id,
            'failed_quiz' => !!$courseModule->user_quiz_failed_id
        ];
    }

    public function includeLessons(CourseModule $courseModule)
    {
        return $this->collection($courseModule->lessons, new IuLessonTransformer($courseModule->lessons, $this->passedPreviousLevel));
    }
}
