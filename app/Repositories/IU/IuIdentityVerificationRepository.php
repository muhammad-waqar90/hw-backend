<?php

namespace App\Repositories\IU;

use App\DataObject\IdentityVerificationStatusData;
use App\Models\IdentityVerification;
use App\Models\ImageVerificationDetail;

class IuIdentityVerificationRepository
{

    private IdentityVerification $identityVerification;

    public function __construct(IdentityVerification $identityVerification)
    {
        $this->identityVerification = $identityVerification;
    }

    public function init($userId, $fullPath)
    {
        $this->identityVerification->updateOrCreate([
            'user_id'   => $userId
        ],
        [
           'status'     => IdentityVerificationStatusData::COMPLETED, //need to refactor when document verification is implemented
           'identity_file' => $fullPath
        ]);
    }

    public static function getByUserId($userId)
    {
        return identityVerification::where('user_id', $userId)->first();
    }

    /**
     * Detected Face
     * @param int $identityVerificationId
     * @param array $faceDetails
    */
    public static function storeDetectFacesDetail(int $identityVerificationId, array $faceDetails)
    {
        return ImageVerificationDetail::create(
            [
                'identity_verification_id' => $identityVerificationId,
                'age_range_low' => $faceDetails['AgeRange']['Low'],
                'age_range_high' => $faceDetails['AgeRange']['High'],
                'smile' => $faceDetails['Smile']['Value'],
                'smile_confidence' => $faceDetails['Smile']['Confidence'],
                'eye_glasses' => $faceDetails['Eyeglasses']['Value'],
                'eye_glasses_confidence' => $faceDetails['Eyeglasses']['Confidence'],
                'sun_glasses' => $faceDetails['Sunglasses']['Value'],
                'sun_glasses_confidence' => $faceDetails['Sunglasses']['Confidence'],
                'gender' => $faceDetails['Gender']['Value'],
                'gender_confidence' => $faceDetails['Gender']['Confidence'],
                'beard' => $faceDetails['Beard']['Value'],
                'beard_confidence' => $faceDetails['Beard']['Confidence'],
                'mustache' => $faceDetails['Mustache']['Value'],
                'mustache_confidence' => $faceDetails['Mustache']['Confidence'],
                'eyes_open' => $faceDetails['EyesOpen']['Value'],
                'eyes_open_confidence' => $faceDetails['EyesOpen']['Confidence'],
                'confidence' => $faceDetails['Confidence'],
            ]
        );
    }

     /**
     * Detected Face
     * @param object $identityVerification
    */
    public function identityVerificationResponse($identityVerification)
    {
        return [
            'verified'  => $identityVerification && $identityVerification->status === IdentityVerificationStatusData::COMPLETED,
            'status'    => $identityVerification ? $identityVerification->status : IdentityVerificationStatusData::PENDING
        ];
    }
}
