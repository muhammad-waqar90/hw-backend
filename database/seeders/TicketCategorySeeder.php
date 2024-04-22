<?php

namespace Database\Seeders;

use App\DataObject\Tickets\TicketCategoryData;
use App\Models\TicketCategory;
use Illuminate\Database\Seeder;

class TicketCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TicketCategory::updateOrCreate(
            ['id'            => TicketCategoryData::SYSTEM],
            ['name'          => 'System']
        );
        TicketCategory::updateOrCreate(
            ['id'            => TicketCategoryData::CONTENT],
            ['name'          => 'Content']
        );
        TicketCategory::updateOrCreate(
            ['id'            => TicketCategoryData::REFUND],
            ['name'          => 'Refund']
        );
        TicketCategory::updateOrCreate(
            ['id'            => TicketCategoryData::GDPR],
            ['name'          => 'GDPR']
        );
        TicketCategory::updateOrCreate(
            ['id'            => TicketCategoryData::LESSON_QA],
            ['name'          => 'Lecture Q&A']
        );
    }
}
