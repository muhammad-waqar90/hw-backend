<?php


namespace App\Jobs\IU;


use App\DataObject\IdentityVerificationStatusData;
use App\Repositories\IU\IuIdentityVerificationRepository;
use App\Services\AWS\AmazonRekognitionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class IuIdentityVerificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $userId;
    private $identityVerification;
    private $imageVerificationDetail;

    public function __construct($userId)
    {
        $this->userId = $userId;
        $this->identityVerification = IuIdentityVerificationRepository::getByUserId($userId);
    }

    public function handle(IuIdentityVerificationRepository $identityVerificationRepository)
    {
        $this->identityVerification = $identityVerificationRepository->getByUserId($this->userId);
        if(!$this->identityVerification && $this->identityVerification->status !== IdentityVerificationStatusData::PROCESSING)
            return false;

        if(!$this->isValid())
            return $this->failed();

        $this->handleSuccess();
    }

    public function isValid()
    {
        $amazonRekognitionService = new AmazonRekognitionService();
        $this->imageVerificationDetail = $amazonRekognitionService->detectFaces($this->identityVerification->identity_file);

        return !!$this->imageVerificationDetail;
    }

    public function handleSuccess()
    {
        $newPath = str_replace('/tmp/', '/valid/', $this->identityVerification->identity_file);
        Storage::disk(config('filesystems.cloud'))->move($this->identityVerification->identity_file, $newPath);

        $this->identityVerification->identity_file = $newPath;
        $this->identityVerification->status = IdentityVerificationStatusData::COMPLETED;
        $this->identityVerification->save();

        IuIdentityVerificationRepository::storeDetectFacesDetail($this->identityVerification->id, $this->imageVerificationDetail);
    }

    public function failed(Throwable $exception = null)
    {
        $this->identityVerification->status = IdentityVerificationStatusData::FAILED;
        $this->identityVerification->save();

        Storage::disk(config('filesystems.cloud'))->delete($this->identityVerification->identity_file);

        return false;
    }
}
