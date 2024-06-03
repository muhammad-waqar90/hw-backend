<?php

namespace App\Exports\Excel\GDPR;

use App\Models\Certificate;
use App\Traits\HierarchyTrait;
use App\Transformers\IU\Certificate\IuCertificateTransformer;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CertificatesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable, HierarchyTrait;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function query()
    {
        return Certificate::where('user_id', $this->userId);
    }

    public function headings(): array
    {
        return [
            'id',
            'entity_type',
            'entity_name',
            'created_at',
            'updated_at',
        ];
    }

    public function prepareRows($rows)
    {
        return $rows->transform(function ($certificate) {
            $transform = collect(fractal($certificate, new IuCertificateTransformer()))->toArray();
            $certificate->entity_type = $transform['hierarchy']['type'];
            $certificate->entity_name = $this->getEntityHierarchyNameExport($transform['hierarchy']);

            return $certificate;
        });
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->entity_type,
            $row->entity_name,
            $row->created_at,
            $row->updated_at,
        ];
    }
}
