<?php

namespace App\Exports\Excel\GDPR;

use App\Models\Refund;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class RefundsExport implements FromQuery, WithHeadings, WithMapping, WithStrictNullComparison
{
    use Exportable;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function query()
    {
        return Refund::with('purchaseItem')->where('user_id', $this->userId);
    }

    public function headings(): array
    {
        return [
            'id',
            'amount',
            'entity_type',
            'entity_name',
            'created_at',
            'updated_at',
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->purchaseItem->amount,
            $row->purchaseItem->entity_type,
            $row->purchaseItem->entity_name,
            $row->created_at,
            $row->updated_at,
        ];
    }
}
