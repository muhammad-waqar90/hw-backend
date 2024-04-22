<?php

namespace App\Exports\Excel\GDPR;

use App\Models\Notification;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class UserNotificationsExport implements FromQuery, WithHeadings, WithMapping, WithStrictNullComparison
{
    use Exportable;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function query()
    {
        return Notification::where('user_id', $this->userId);
    }

    public function headings(): array
    {
        return [
            'id',
            'title',
            'description',
            'read',
            'created_at',
            'updated_at'
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->title,
            $row->description,
            $row->read,
            $row->created_at,
            $row->updated_at
        ];
    }
}
