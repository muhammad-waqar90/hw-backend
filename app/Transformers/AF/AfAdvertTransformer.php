<?php

namespace App\Transformers\AF;

use App\Models\Advert;
use League\Fractal\TransformerAbstract;
use App\Traits\FileSystemsCloudTrait;
use App\DataObject\AdvertData;

class AfAdvertTransformer extends TransformerAbstract
{
    use FileSystemsCloudTrait;

    /**
     * A Fractal transformer.
     * @param Advert $advert
     * @return array
     */
    public function transform(Advert $advert)
    {
        return [
            'id'   => $advert->id,
            'name' => $advert->name,
            'img'  => $this->generateS3Link('adverts/images/'.$advert->img, AdvertData::DEFAULT_ADVERT_EXPIRY_DAYS),
            'priority' => $advert->priority
        ];
    }
}
