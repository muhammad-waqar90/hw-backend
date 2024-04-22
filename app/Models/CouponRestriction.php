<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponRestriction extends Model
{
    use HasFactory;

    protected $guarded = [
        'id', 'created_at', 'updated_at'
    ];

    public function coupon()
    {
        return $this->belongsTo('App\Models\Coupon');
    }

    /**
     * Get the parent entity model.
        * Course
        * ...
     */
    public function entity()
    {
        return $this->morphTo();
    }
}
