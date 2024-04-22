<?php

namespace App\Transformers\AF;

use App\Models\CourseModule;
use App\Repositories\AF\AfCourseModuleRepository;
use App\Traits\FileSystemsCloudTrait;
use League\Fractal\TransformerAbstract;

class AfCourseModuleTransformer extends TransformerAbstract
{
    use FileSystemsCloudTrait;

    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        'lessons',
        'quiz',
        'book',
    ];

    /**
     * A Fractal transformer.
     *
     * @param CourseModule $courseModule
     * @return array
     */
    public function transform(CourseModule $courseModule)
    {
        return [
            'id'                =>  $courseModule->id,
            'course_id'         =>  $courseModule->course_id,
            'course_level_id'   =>  $courseModule->course_level_id,
            'order_id'          =>  $courseModule->order_id,
            'name'              =>  $courseModule->name,
            'description'       =>  $courseModule->description,
            'img'               =>  $courseModule->img ? $this->generateS3Link(AfCourseModuleRepository::getThumbnailS3StoragePath() . $courseModule->img, 1) : null,
            'video_preview'     =>  $courseModule->video_preview,
            'ebook_price'       =>  $courseModule->ebook_price,
            'has_ebook'         =>  $courseModule->has_ebook,
            'created_at'        =>  $courseModule->created_at,
            'updated_at'        =>  $courseModule->updated_at,
        ];
    }

    public function includeLessons($courseModule)
    {
        return $this->collection($courseModule->lessons, new AfLessonTransformer());
    }

    public function includeQuiz($courseModule)
    {
        return $this->collection($courseModule->quiz, new AfQuizTransformer());
    }

    public function includeBook($courseModule)
    {
        // return $courseModule->book ? $this->item($courseModule->book, new AfCourseModuleBookTransformer()) : $this->null();
        if ($courseModule->book):
            $bookCollection = collect([$courseModule->book]);
            return $this->collection($bookCollection, new AfCourseModuleBookTransformer());
        else:
            return $this->null();
        endif;
    }
}
