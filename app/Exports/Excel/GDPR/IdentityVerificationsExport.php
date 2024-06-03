<?php

namespace App\Exports\Excel\GDPR;

use App\Models\IdentityVerification;
use App\Traits\FileSystemsCloudTrait;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class IdentityVerificationsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;
    use FileSystemsCloudTrait;

    public function __construct(int $userId, string $tmpExportGdprDirectory)
    {
        $this->userId = $userId;
        $this->tmpExportGdprDirectory = $tmpExportGdprDirectory;
    }

    public function query()
    {
        return IdentityVerification::where('user_id', $this->userId);
    }

    public function headings(): array
    {
        return [
            'id',
            'user_id',
            'identity_file',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    public function map($row): array
    {
        $this->getFile($this->tmpExportGdprDirectory, $row->identity_file);

        return [
            $row->id,
            $row->user_id,
            $row->identity_file,
            $row->status,
            $row->created_at,
            $row->updated_at,
        ];
    }
}
