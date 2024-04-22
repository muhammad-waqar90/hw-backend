<?php

namespace App\Exports\Excel\GDPR;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomerInfoExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function query()
    {
        return Customer::where('user_id', $this->userId);
    }

    public function headings(): array
    {
        return [
            'id',
            'stripe_id',
            'card_brand',
            'card_last_four',
            'trial_ends_at',
            'created_at',
            'updated_at'
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->stripe_id,
            $row->card_brand,
            $row->card_last_four,
            $row->trial_ends_at,
            $row->created_at,
            $row->updated_at,
        ];
    }
}
