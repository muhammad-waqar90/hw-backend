<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMessage extends Model
{
    use HasFactory;

    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];

    protected $hidden = [
        'pivot',
    ];

    /**
     * Get the user that owns the TicketMessage
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
