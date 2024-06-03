<?php

namespace App\Models;

use App\DataObject\Tickets\TicketMessageTypeData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [
        'id', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected $hidden = [
        'pivot', 'deleted_at',
    ];

    public function latestTicketMessage()
    {
        return $this->hasOne(TicketMessage::class)->latest();
    }

    public function ticketMessages()
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function status()
    {
        return $this->hasOne(TicketStatus::class, 'id', 'ticket_status_id');
    }

    public function hasAdminReply()
    {
        return $this->hasOne(TicketMessage::class)->where('type', TicketMessageTypeData::ADMIN_MESSAGE)->latest();
    }

    public function lesson()
    {
        return $this->belongsToMany(Lesson::class); // ;) hack with relation
    }
}
