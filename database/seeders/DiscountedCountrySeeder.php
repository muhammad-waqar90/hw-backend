<?php

namespace Database\Seeders;

use App\DataObject\DiscountedCountryData;
use App\Models\DiscountedCountry;
use Illuminate\Database\Seeder;

class DiscountedCountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DiscountedCountry::updateOrCreate(
            [
                'id' => DiscountedCountryData::DISCOUNTED_COUNTRY_UK
            ],
            [
                'name' => 'United Kingdom',
                'iso_country_code' => 'GBR',
                'iso_currency_code' => 'GBP'
            ]
        );
    }
}
