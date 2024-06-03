<?php

namespace App\Exports\Excel\GDPR;

use App\Models\LessonNote;
use Illuminate\Support\Facades\Lang;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LessonNotesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function query()
    {
        return LessonNote::with('lesson', 'lesson.courseModule', 'lesson.courseModule.courseLevel', 'lesson.courseModule.course')->where('user_id', $this->userId);
    }

    public function headings(): array
    {
        return [
            'id',
            'name',
            'content',
            'created_at',
            'updated_at',
        ];
    }

    public function prepareRows($rows)
    {
        return $rows->transform(function ($lessonNote) {
            $lessonNote->name = $lessonNote->lesson->courseModule->course->name.' '.
                Lang::get('iu.course.level').' '.$lessonNote->lesson->courseModule->courseLevel->value.' '.
                $lessonNote->lesson->courseModule->name.' '.
                $lessonNote->lesson->name;

            return $lessonNote;
        });
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->name,
            $row->content,
            $row->created_at,
            $row->updated_at,
        ];
    }
}
