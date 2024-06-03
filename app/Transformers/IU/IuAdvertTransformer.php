<?php

namespace App\Transformers\IU;

use App\DataObject\AdvertData;
use App\Models\Advert;
use App\Traits\FileSystemsCloudTrait;
use League\Fractal\TransformerAbstract;

class IuAdvertTransformer extends TransformerAbstract
{
    use FileSystemsCloudTrait;

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Advert $advert)
    {
        return [
            'id' => $advert->id,
            'name' => $advert->name,
            'img' => $this->generateS3Link('adverts/images/'.$advert->img, AdvertData::DEFAULT_ADVERT_EXPIRY_DAYS),
            'url' => $advert->url,
        ];
    }
}
