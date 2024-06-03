<?php

namespace App\Exports\Excel\GDPR;

use App\DataObject\UserProgressData;
use App\Models\UserProgress;
use App\Traits\HierarchyTrait;
use App\Transformers\IU\CourseHierarchy\IuCourseLevelHierarchyTransformer;
use App\Transformers\IU\CourseHierarchy\IuCourseModuleHierarchyTransformer;
use App\Transformers\IU\CourseHierarchy\IuLessonHierarchyTransformer;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UserPlatformProgress implements FromQuery, WithHeadings, WithMapping
{
    use Exportable, HierarchyTrait;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function query()
    {
        return UserProgress::where('user_id', $this->userId);
    }

    public function headings(): array
    {
        return [
            'id',
            'entity_type',
            'name',
            'progress',
            'created_at',
            'updated_at',
        ];
    }

    public function prepareRows($rows)
    {
        return $rows->transform(function ($userProgress) {
            $userProgress->entity_name = $this->getEntityName($userProgress);

            return $userProgress;
        });
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->entity_type,
            $row->entity_name,
            $row->progress,
            $row->created_at,
            $row->updated_at,
        ];
    }

    public function getEntityName($userProgress)
    {
        $hierarchy = [];
        if ($userProgress->entity_type == UserProgressData::ENTITY_LESSON) {
            $userProgress->with('lesson');
            $hierarchy = collect(fractal($userProgress->lesson, new IuLessonHierarchyTransformer()))->toArray();
        } elseif ($userProgress->entity_type == UserProgressData::ENTITY_COURSE_MODULE) {
            $userProgress->with('courseModule');
            $hierarchy = collect(fractal($userProgress->courseModule, new IuCourseModuleHierarchyTransformer()))->toArray();
        } elseif ($userProgress->entity_type == UserProgressData::ENTITY_COURSE_LEVEL) {
            $userProgress->with('courseLevel');
            $hierarchy = collect(fractal($userProgress->courseLevel, new IuCourseLevelHierarchyTransformer()))->toArray();
        } elseif ($userProgress->entity_type == UserProgressData::ENTITY_COURSE) {
            $userProgress->with('course');

            return $userProgress->course->name;
        }

        return $this->getEntityHierarchyNameExport($hierarchy);
    }
}
