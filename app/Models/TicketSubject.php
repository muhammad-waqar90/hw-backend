<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketSubject extends Model
{
    use HasFactory;

    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'pivot',
    ];

    public function ticketCategory()
    {
        return $this->belongsTo(TicketCategory::class);
    }
}
