<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountedCountryRange extends Model
{
    use HasFactory;

    protected $fillable = [
        'discount_option',
        'discount_range',
    ];

    protected $casts = [
        'discount_range' => 'array',
    ];

    public function discountedCountry()
    {
        return $this->belongsTo(DiscountedCountry::class);
    }
}
