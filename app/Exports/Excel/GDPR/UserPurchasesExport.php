<?php

namespace App\Exports\Excel\GDPR;

use App\Models\PurchaseItem;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class UserPurchasesExport implements FromQuery, WithHeadings, WithMapping, WithStrictNullComparison
{
    use Exportable;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function query()
    {
        return PurchaseItem::select('purchase_items.*', 'ph.user_id')
            ->leftJoin('purchase_histories as ph', function($query) {
                return $query->on('purchase_items.purchase_history_id', '=', 'ph.id');
            })
            ->where('ph.user_id', $this->userId);
    }

    public function headings(): array
    {
        return [
            'id',
            'amount',
            'entity_type',
            'entity_name',
            'status',
            'created_at',
            'updated_at'
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->amount,
            $row->entity_type,
            $row->entity_name,
            $row->status,
            $row->created_at,
            $row->updated_at
        ];
    }
}
