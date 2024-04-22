<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountedCountry extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'iso_country_code',
        'iso_currency_code'
    ];

    public function discountRanges()
    {
        return $this->hasMany(DiscountedCountryRange::class);
    }
}