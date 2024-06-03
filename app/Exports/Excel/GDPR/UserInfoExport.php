<?php

namespace App\Exports\Excel\GDPR;

use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UserInfoExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function query()
    {
        return User::with('userProfile')->where('id', $this->userId);
    }

    public function headings(): array
    {
        return [
            'id',
            'name',
            'last_active',
            'created_at',
            'updated_at',
            'first_name',
            'last_name',
            'email',
            'date_of_birth',
            'gender',
            'occupation',
            'country',
            'city',
            'address',
            'postal_code',
            'phone_number',
            'facebook_url',
            'instagram_url',
            'twitter_url',
            'linkedin_url',
            'snapchat_url',
            'youtube_url',
            'pinterest_url',
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->name,
            $row->last_active,
            $row->created_at,
            $row->updated_at,
            $row->first_name,
            $row->last_name,
            $row->userProfile->email,
            $row->userProfile->date_of_birth,
            $row->userProfile->gender,
            $row->userProfile->occupation,
            $row->userProfile->country,
            $row->userProfile->city,
            $row->userProfile->address,
            $row->userProfile->postal_code,
            $row->userProfile->phone_number,
            $row->userProfile->facebook_url,
            $row->userProfile->instagram_url,
            $row->userProfile->twitter_url,
            $row->userProfile->linkedin_url,
            $row->userProfile->snapchat_url,
            $row->userProfile->youtube_url,
            $row->userProfile->pinterest_url,
        ];
    }
}
