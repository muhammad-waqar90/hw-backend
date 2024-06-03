<?php

namespace App\Repositories\AF;

use App\Models\Event;

class AfEventRepository
{
    private Event $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public static function getImageS3StoragePath()
    {
        return 'events/images/';
    }

    public function createEvent($title, $description, $type, $img, $url, $startDate, $endDate)
    {
        return $this->event->create([
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'img' => $img,
            'url' => $url,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    public function getEventList($searchText = null, $type = null)
    {
        return $this->event
            ->when($searchText, function ($query) use ($searchText) {
                $query->where('title', 'LIKE', "%$searchText%");
            })
            ->when($type, function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->oldest('start_date')
            ->latest('id')
            ->paginate(20);
    }

    public function getEvent($id)
    {
        return $this->event->where('id', $id)->first();
    }

    public function updateEvent($id, $title, $description, $type, $img, $url, $startDate, $endDate)
    {
        return $this->event->where('id', $id)->update([
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'img' => $img,
            'url' => $url,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    public function deleteEvent($id)
    {
        return $this->event->where('id', $id)->delete();
    }
}
