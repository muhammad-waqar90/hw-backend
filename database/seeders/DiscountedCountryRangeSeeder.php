<?php

namespace Database\Seeders;

use App\DataObject\AF\SalaryScale\SalaryScaleData;
use App\DataObject\DiscountedCountryData;
use App\Models\DiscountedCountryRange;
use Illuminate\Database\Seeder;

class DiscountedCountryRangeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $options = [
            [
                'id' => SalaryScaleData::DISCOUNT_OPTION_A,
                'discounted_country_id' => DiscountedCountryData::DISCOUNTED_COUNTRY_UK,
                'discount_option' => 'a',
                'discount_range' => ['15,000'],
                'discount_percentage' => 100
            ],
            [
                'id' => SalaryScaleData::DISCOUNT_OPTION_B,
                'discounted_country_id' => DiscountedCountryData::DISCOUNTED_COUNTRY_UK,
                'discount_option' => 'b',
                'discount_range' => ['15,000', '29,999'],
                'discount_percentage' => 75
            ],
            [
                'id' => SalaryScaleData::DISCOUNT_OPTION_C,
                'discounted_country_id' => DiscountedCountryData::DISCOUNTED_COUNTRY_UK,
                'discount_option' => 'c',
                'discount_range' => ['30,000', '50,000'],
                'discount_percentage' => 50
            ],
            [
                'id' => SalaryScaleData::DISCOUNT_OPTION_D,
                'discounted_country_id' => DiscountedCountryData::DISCOUNTED_COUNTRY_UK,
                'discount_option' => 'd',
                'discount_range' => [
                    '50,000+'
                ],
                'discount_percentage' => 0
            ],

        ];

        foreach ($options as $option) :
            DiscountedCountryRange::updateOrCreate(
                [
                    'id' => $option['id']
                ],
                [
                    'discounted_country_id' => $option['discounted_country_id'],
                    'discount_option' => $option['discount_option'],
                    'discount_range' => $option['discount_range'],
                    'discount_percentage' => $option['discount_percentage']
                ]
            );
        endforeach;
    }
}
