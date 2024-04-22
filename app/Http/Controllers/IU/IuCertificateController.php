<?php

namespace App\Http\Controllers\IU;

use App\Http\Controllers\Controller;
use App\Repositories\IU\IuCertificateRepository;
use App\Traits\HierarchyTrait;
use App\Transformers\IU\Certificate\IuCertificateTransformer;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Image;
use PDF;

class IuCertificateController extends Controller
{
    use HierarchyTrait;

    private IuCertificateRepository $iuCertificateRepository;

    public function __construct(IuCertificateRepository $iuCertificateRepository)
    {
        $this->iuCertificateRepository = $iuCertificateRepository;
    }

    public function getMyCertificatesList(Request $request)
    {
        $userId = $request->user()->id;
        $data = $this->iuCertificateRepository->getMyCertificateList($userId);

        $fractal = fractal($data->getCollection(), new IuCertificateTransformer());
        $data->setCollection(collect($fractal));

        return response()->json($data, 200);
    }

    public function getCertificate($id, Request $request)
    {
        $certificate = $request->certificate;
        $data = fractal($certificate, new IuCertificateTransformer());

        return response()->json($data, 200);
    }

    public function downloadCertificate($id, Request $request)
    {
        $certificate = fractal($request->certificate, new IuCertificateTransformer())->toArray();
        $parseDate = Carbon::createFromFormat('Y-m-d H:i:s', $certificate['created_at']);

        $fullDate = $parseDate->format('d M Y');
        $dateWithFullMonth = $parseDate->format('F Y');
        $year = $parseDate->format('Y');


        $pdf = PDF::loadView('pdfs.certificates.certificate', [
            'userProfile'               => $request->user(),
            'entityName'                => $this->getCourseHierarchyNameCertificate($certificate['hierarchy'], $certificate['type']),
            'certificateCreatedAt'      => $fullDate,
            'certificateDateWithMonth'  =>  $dateWithFullMonth,
            'year'                      =>  $year,
            'signatureImage'            =>  Image::make($this->iuCertificateRepository->getSignatureImage())->encode('data-url'),
            'dateImage'                 =>  Image::make($this->iuCertificateRepository->getDateImage())->encode('data-url'),
            'logoImage'                 =>  Image::make($this->iuCertificateRepository->getLogoImage())->encode('data-url')
        ]);

        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('certificate.pdf');
    }
}
