<?php

namespace App\Repositories\IU;

use App\Models\Event;
use Illuminate\Support\Carbon;

class IuEventsRepository
{
    private Event $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function getEventsForDates($from, $to, $type = null)
    {
        return $this->event->select('id', 'title', 'type', 'start_date', 'end_date')
            ->where(function ($query) use ($from, $to) {
                $query
                    ->where('start_date', '>=', Carbon::createFromFormat('Y-m-d', $from)->startOfDay())
                    ->where('start_date', '<=', Carbon::createFromFormat('Y-m-d', $to)->endOfDay())
                    ->orWhere(function ($query) use ($from, $to) {
                        $query
                            ->where('end_date', '>=', Carbon::createFromFormat('Y-m-d', $from)->startOfDay())
                            ->where('end_date', '<=', Carbon::createFromFormat('Y-m-d', $to)->endOfDay());
                    });
            })
            ->when($type, function ($query) use ($type) {
                $query->whereIn('type', $type);
            })
            ->get();
    }

    public function getEventById($id)
    {
        return $this->event->where('id', $id)->first();
    }
}
