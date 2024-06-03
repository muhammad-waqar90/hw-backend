<?php

namespace App\Exports\Excel\GDPR;

use App\Models\EbookAccess;
use Illuminate\Support\Facades\Lang;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LectureNotesAccessesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function query()
    {
        return EbookAccess::with('courseModule', 'courseModule.courseLevel', 'courseModule.course')->where('user_id', $this->userId);
    }

    public function headings(): array
    {
        return [
            'id',
            'name',
            'created_at',
            'updated_at',
        ];
    }

    public function prepareRows($rows)
    {
        return $rows->transform(function ($ebookAccess) {
            $ebookAccess->name = $ebookAccess->courseModule->course->name.' '.
                Lang::get('iu.course.level').' '.$ebookAccess->courseModule->courseLevel->value.' '.
                $ebookAccess->courseModule->name;

            return $ebookAccess;
        });
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->name,
            $row->created_at,
            $row->updated_at,
        ];
    }
}
