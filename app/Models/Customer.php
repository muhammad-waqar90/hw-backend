<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Billable;

class Customer extends Model
{
    use Billable, HasFactory;

    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];
}
