<?php

namespace App\Exports\Excel\GDPR;

use App\DataObject\QuizData;
use App\Models\ExamAccess;
use App\Traits\HierarchyTrait;
use App\Transformers\IU\CourseHierarchy\IuCourseLevelHierarchyTransformer;
use App\Transformers\IU\CourseHierarchy\IuCourseModuleHierarchyTransformer;
use App\Transformers\IU\CourseHierarchy\IuLessonHierarchyTransformer;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExamAccessesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable, HierarchyTrait;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function query()
    {
        return ExamAccess::with('quiz')->where('user_id', $this->userId);
    }

    public function headings(): array
    {
        return [
            'id',
            'entity_type',
            'entity_name',
            'attempts_left',
            'created_at',
            'updated_at'
        ];
    }

    public function prepareRows($rows)
    {
        return $rows->transform(function ($examAccess) {
            $examAccess->entity_name = $this->getEntityName($examAccess->quiz);

            return $examAccess;
        });
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->quiz->entity_type,
            $row->entity_name,
            $row->attempts_left,
            $row->created_at,
            $row->updated_at
        ];
    }

    public function getEntityName($userQuiz)
    {
        $hierarchy = [];
        if($userQuiz->entity_type == QuizData::ENTITY_LESSON) {
            $userQuiz->with('lesson');
            $hierarchy = collect(fractal($userQuiz->lesson, new IuLessonHierarchyTransformer()))->toArray();
        } elseif($userQuiz->entity_type == QuizData::ENTITY_COURSE_MODULE) {
            $userQuiz->with('courseModule');
            $hierarchy = collect(fractal($userQuiz->courseModule, new IuCourseModuleHierarchyTransformer()))->toArray();
        } elseif($userQuiz->entity_type == QuizData::ENTITY_COURSE_LEVEL) {
            $userQuiz->with('courseLevel');
            $hierarchy = collect(fractal($userQuiz->courseLevel, new IuCourseLevelHierarchyTransformer()))->toArray();
        }

        return $this->getEntityHierarchyNameExport($hierarchy);
    }
}
