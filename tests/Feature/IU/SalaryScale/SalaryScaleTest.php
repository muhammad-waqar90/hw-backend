<?php

namespace Tests\Feature\IU\SalaryScale;

use App\Models\DiscountedCountry;
use App\Models\DiscountedCountryRange;
use App\Models\User;
use App\Models\UserSalaryScale;
use Tests\TestCase;

class SalaryScaleTest extends TestCase
{
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
        $this->actingAs($this->user);
    }

    public function testGetDiscountedCountries()
    {
        $response = $this->json('GET', '/api/iu/salary-scales/discounted-countries');

        $response->assertStatus(200);
    }

    public function testCreateSalaryScales()
    {
        $discountedCountryId = DiscountedCountry::query()->first()->id;
        $discountedCountryRangeId = DiscountedCountryRange::query()->first()->id;


        $response = $this->json('POST', '/api/iu/salary-scales', [
            'discounted_country_id'         => $discountedCountryId,
            'discounted_country_range_id'   => $discountedCountryRangeId,
            'declaration'                   => true
        ]);

        $response->assertStatus(200);
    }

    public function testUpdateSalaryScales()
    {
        UserSalaryScale::factory()->withUserId($this->user->id)->create();
        $discountedCountry = DiscountedCountry::query()->first();
        $discountedCountryRange = DiscountedCountryRange::query()->latest()->first();

        $response = $this->json('PUT', '/api/iu/salary-scales', [
            'discounted_country_id'         => $discountedCountry->id,
            'discounted_country_range_id'   => $discountedCountryRange->id
        ]);

        $response->assertStatus(200);
    }
}
