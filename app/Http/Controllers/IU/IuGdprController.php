<?php

namespace App\Http\Controllers\IU;

use App\DataObject\GDPRStatusData;
use App\Http\Controllers\Controller;
use App\Repositories\GdprRepository;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;

class IuGdprController extends Controller
{
    private GdprRepository $gdprRepository;

    public function __construct(GdprRepository $gdprRepository)
    {
        $this->gdprRepository = $gdprRepository;
    }

    public function downloadGdprZip($uuid)
    {
        $gdprRequest = $this->gdprRepository->getGdprRequestByUuid($uuid);

        if (!$gdprRequest)
            return Lang::get('general.notFound');
        if ($gdprRequest->status == GDPRStatusData::EXPIRED)
            return Lang::get('iu.gdprRequest.downloadLinkExpired');

        $name = $uuid . '.zip';
        $pathToFile = 'GDPR/' . $name;

        if (!Storage::disk('s3')->exists($pathToFile)) {
            return Lang::get('general.notFound');
        }

        if ($file = Storage::disk('s3')->get($pathToFile)) {
            $this->gdprRepository->markGdprRequestAsDownloaded($uuid);

            return response(
                $file,
                200,
                [
                    'Content-Type'        => 'application/zip',
                    'Content-Disposition' => 'attachment; filename="' . $name . '"',
                ]
            );
        }

        return "Oops! Something went wrong";
    }
}
