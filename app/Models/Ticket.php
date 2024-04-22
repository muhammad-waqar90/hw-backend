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
        'id', 'created_at', 'updated_at', 'deleted_at'
    ];

    protected $hidden = [
        'pivot', 'deleted_at'
    ];

    public function latestTicketMessage()
    {
        return $this->hasOne('App\Models\TicketMessage')->latest();
    }

    public function ticketMessages()
    {
        return $this->hasMany('App\Models\TicketMessage');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    public function status()
    {
        return $this->hasOne('App\Models\TicketStatus', 'id', 'ticket_status_id');
    }

    public function hasAdminReply()
    {
        return $this->hasOne('App\Models\TicketMessage')->where('type', TicketMessageTypeData::ADMIN_MESSAGE)->latest();
    }

    public function lesson()
    {
        return $this->belongsToMany('App\Models\Lesson'); // ;) hack with relation
    }
}
