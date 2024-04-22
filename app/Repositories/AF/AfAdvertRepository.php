<?php

namespace App\Repositories\AF;

use App\Models\Advert;
use Batch;
use Carbon\Carbon;
use App\DataObject\AdvertData;

class AfAdvertRepository
{

    private Advert $advert;

    public function __construct(Advert $advert)
    {
        $this->advert = $advert;
    }

    public function getAdvertList($searchText = null, $status = null)
    {
        return $this->advert
            ->when($searchText, function ($query) use ($searchText) {
                $query->where('name', 'LIKE', "%$searchText%");
            })
            ->when($status, function ($query) use ($status) {
                return $query->whereStatus($status);
            })
            ->orderBy('priority', 'ASC')
            ->orderBy('id', 'ASC');
    }

    public function getAdvert($id)
    {
        return $this->advert->where('id', $id)->first();
    }

    public function createAdvert($name, $url, $img, $status, $expires_at)
    {
        return $this->advert->create([
            'name'          => $name,
            'url'           => $url,
            'img'           => $img,
            'expires_at'    => $expires_at,
            'status'        => $status,
            'priority'      => AdvertData::DEFAULT_PRIORITY
        ]);
    }

    public function updateAdvert($id, $name, $img, $url, $priority, $expires_at, $status)
    {
        return $this->advert->where('id', $id)->update([
            'name'          => $name,
            'img'           => $img,
            'url'           => $url,
            'priority'      => $priority,
            'expires_at'    => $expires_at,
            'status'        => $status
        ]);
    }

    public function sortingAdvert(array $data)
    {
        Batch::update(new Advert, $data, 'id');
    }

    public function deactivateExpiredAdverts()
    {
        $this->advert->where("expires_at", '<', Carbon::now())
            ->where('status', '=', AdvertData::STATUS_ACTIVE)
            ->update([
                'status'   => AdvertData::STATUS_INACTIVE,
                'priority' => AdvertData::DEFAULT_PRIORITY
            ]);
    }
}
