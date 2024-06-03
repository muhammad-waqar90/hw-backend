<?php

namespace App\Exports\Excel\GDPR;

use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CourseEnrollmentsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function query()
    {
        return User::find($this->userId)->enrolledCourses();
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

    public function map($row): array
    {
        return [
            $row->pivot->id,
            $row->name,
            $row->pivot->created_at,
            $row->pivot->updated_at,
        ];
    }
}
