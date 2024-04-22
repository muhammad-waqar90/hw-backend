<?php

namespace Database\Seeders;

use App\DataObject\Tickets\TicketStatusData;
use App\Models\TicketStatus;
use Illuminate\Database\Seeder;

class TicketStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TicketStatus::updateOrCreate(
            ['id'   => TicketStatusData::UNCLAIMED],
            ['name' => 'unclaimed']
        );
        TicketStatus::updateOrCreate(
            ['id'   => TicketStatusData::IN_PROGRESS],
            ['name' => 'in_progress']
        );
        TicketStatus::updateOrCreate(
            ['id'   => TicketStatusData::RESOLVED],
            ['name' => 'resolved']
        );
        TicketStatus::updateOrCreate(
            ['id'   => TicketStatusData::REOPENED],
            ['name' => 'reopened']
        );
        TicketStatus::updateOrCreate(
            ['id'   => TicketStatusData::ON_HOLD],
            ['name' => 'on_hold']
        );
    }
}
