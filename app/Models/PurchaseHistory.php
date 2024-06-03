<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseHistory extends Model
{
    use HasFactory;

    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];

    public function purchaseItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Get the parent entity model.
     * StripePayment
     * InAppPayment
     */
    public function entity()
    {
        return $this->morphTo();
    }

    public function shippingDetails()
    {
        return $this->hasMany(ShippingDetail::class);
    }
}
