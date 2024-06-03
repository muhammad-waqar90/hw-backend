<?php

namespace App\Transformers\AF;

use App\Models\Event;
use App\Repositories\AF\AfEventRepository;
use App\Traits\FileSystemsCloudTrait;
use League\Fractal\TransformerAbstract;

class AfEventTransformer extends TransformerAbstract
{
    use FileSystemsCloudTrait;

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Event $event)
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'type' => $event->type,
            'img' => $event->img ? $this->generateS3Link(AfEventRepository::getImageS3StoragePath().$event->img, 1) : null,
            'url' => $event->url,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
        ];
    }
}
