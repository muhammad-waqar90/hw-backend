<?php

namespace App\Repositories\IU;

use App\Models\Certificate;
use Illuminate\Support\Facades\Storage;

class IuCertificateRepository
{
    private Certificate $certificate;

    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
    }

    public function createCertificate($userId, $entityId, $entityType)
    {
        return $this->certificate->updateOrCreate([
            'user_id' => $userId,
            'entity_id' => $entityId, // course | level | module
            'entity_type' => $entityType,
        ]);
    }

    public function getMyCertificateList($userId)
    {
        return $this->certificate
            ->where('user_id', $userId)
            ->latest()
            ->paginate(10);
    }

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
