<?php


namespace App\Repositories\IU;

use App\Models\Certificate;

use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Log;

class IuCertificateRepository
{

    private Certificate $certificate;

    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
    }

    /**
     * @param $userId
     * @param $entityId
     * @param $entityType
     * @return mixed
     */
    public function createCertificate($userId, $entityId, $entityType)
    {
        return $this->certificate->updateOrCreate([
            'user_id'       => $userId,
            'entity_id'     => $entityId, // course | level | module
            'entity_type'   => $entityType,
        ]);
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function getMyCertificateList($userId)
    {
        return $this->certificate
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
    }

    /**
     * @param $id
     * @return Certificate
     */
    public function getCertificate($id)
    {
        return $this->certificate->find($id);
    }

    public function getSignatureImage(): string
    {
        return Storage::disk('s3')->get('Images/HFS-signature.png');
    }

    public function getDateImage(): string
    {
        return resource_path('views/pdfs/certificates/img/certificateDateImg.png');
    }

    public function getLogoImage(): string
    {
        return resource_path('views/pdfs/certificates/img/hijaz_logo.png');
    }


}
