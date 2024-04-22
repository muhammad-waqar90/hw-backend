<?php

namespace App\Http\Controllers\IU;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use App\Transformers\IU\IuProfileTransformer;
use App\Http\Requests\IU\IuUpdateProfileRequest;
use App\Repositories\IU\IuUserProfileRepository;
// use App\Jobs\IU\IuIdentityVerificationJob;
use App\DataObject\IdentityVerificationStatusData;
use App\Http\Requests\IU\IuCreateUpdateIdentityRequest;
use App\Http\Requests\IU\IuUpdateUserAddressRequest;
use App\Http\Requests\IU\SalaryScale\IuUpdateEnableSalaryScaleFlagRequest;
use App\Repositories\IU\IuIdentityVerificationRepository;
use Illuminate\Support\Facades\Log;

class IuUserProfileController extends Controller
{

    private IuUserProfileRepository $iuUserProfileRepository;
    private IuIdentityVerificationRepository $identityVerificationRepository;

    public function __construct(IuUserProfileRepository $iuUserProfileRepository, IuIdentityVerificationRepository $identityVerificationRepository)
    {
        $this->iuUserProfileRepository = $iuUserProfileRepository;
        $this->identityVerificationRepository = $identityVerificationRepository;
    }

    public function get(Request $request)
    {
        $user = $request->user();
        $userProfile = $user->userProfile;

        $userProfile->first_name = $user->first_name;
        $userProfile->last_name = $user->last_name;

        $fractal = fractal($userProfile, new IuProfileTransformer());
        return response()->json($fractal, 200);
    }

    public function update(IuUpdateProfileRequest $request)
    {
        $user = $request->user();
        $this->iuUserProfileRepository->update($user->userProfile->id, $request);
        return response()->json(['message' => Lang::get('iu.profile.successUpdate')], 200);
    }

    private function initIdentityVerification($identityFile, $user)
    {
        $extension = $identityFile->extension();
        $fullPath = "UserIdentity/tmp/user_$user->id.$extension";
        Storage::disk(config('filesystems.cloud'))->putFileAs('UserIdentity/tmp/', request()->identityFile, "user_$user->id.$extension");

        $this->identityVerificationRepository->init($user->id, $fullPath);
        // IuIdentityVerificationJob::dispatch($user->id)->onQueue('low');
    }

    public function getIdentity(Request $request)
    {
        $user = $request->user();

        $identityVerification = $this->identityVerificationRepository->identityVerificationResponse($user->identityVerification);
        return response()->json($identityVerification, 200);
    }

    public function updateOrCreateIdentity(IuCreateUpdateIdentityRequest $request)
    {
        $user = $request->user();
        $identityFile = $request->file('identityFile');
        $identityVerification = $this->identityVerificationRepository->getByUserId($user->id);
        $identityVerificationStatus = $identityVerification ? $identityVerification->status : IdentityVerificationStatusData::PENDING;

        if ($identityFile && $identityVerificationStatus === IdentityVerificationStatusData::COMPLETED)
            return response()->json(['message' => Lang::get('iu.identityVerification.alreadyVerified')], 400);

        if ($identityFile && $identityVerificationStatus === IdentityVerificationStatusData::PROCESSING)
            return response()->json(['message' => Lang::get('iu.identityVerification.previouslyProcessing')], 400);

        if (!$identityFile && ($identityVerificationStatus === IdentityVerificationStatusData::FAILED || $identityVerificationStatus === IdentityVerificationStatusData::PENDING))
            return response()->json(['message' => Lang::get('iu.identityVerification.required')], 400);

        if ($identityFile && ($identityVerificationStatus === IdentityVerificationStatusData::FAILED || $identityVerificationStatus === IdentityVerificationStatusData::PENDING))
            $this->initIdentityVerification($identityFile, $user);

        return response()->json(['message' => Lang::get('iu.identityVerification.successfullyUploaded')], 200);
    }

    // Update salary scale flag
    public function updateEnableSalaryScaleFlag(IuUpdateEnableSalaryScaleFlagRequest $request)
    {
        try {
            // Logic here
            return $this->iuUserProfileRepository->updateUserEnableSalaryScaleFlag($request);
        } catch (Exception $e) {
            return response()->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function updateUserAddress(IuUpdateUserAddressRequest $request)
    {
        try {
            $this->iuUserProfileRepository->updateUserAddress(
                auth()->user()->id,
                $request->address,
                $request->city,
                $request->country,
                $request->postalCode
            );

            return response()->json(['message' => Lang::get('iu.profile.successUpdate')], 200);
        } catch (Exception $e) {
            Log::error('Exception: IuUserProfileController@updateAddress', [$e->getMessage()]);
            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }
}
