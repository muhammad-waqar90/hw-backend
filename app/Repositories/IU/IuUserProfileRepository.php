<?php

namespace App\Repositories\IU;

use App\Http\Requests\IU\IuUpdateProfileRequest;
use App\Models\UserProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Lang;

class IuUserProfileRepository
{
    private UserProfile $userProfile;

    public function __construct(UserProfile $userProfile)
    {
        $this->userProfile = $userProfile;
    }

    public function update($id, IuUpdateProfileRequest $request)
    {
        return $this->userProfile->where('id', $id)
            ->update([
                'gender' => $request->gender,
                'occupation' => $request->occupation,
                'country' => $request->country,
                'city' => $request->city,
                'address' => $request->address,
                'postal_code' => $request->postalCode,
                'phone_number' => $request->phoneNumber,
                'facebook_url' => $request->facebookUrl,
                'instagram_url' => $request->instagramUrl,
                'twitter_url' => $request->twitterUrl,
                'linkedin_url' => $request->linkedinUrl,
                'snapchat_url' => $request->snapchatUrl,
                'youtube_url' => $request->youtubeUrl,
                'pinterest_url' => $request->pinterestUrl,
            ]);
    }

    public static function getIsProfileCompleted(UserProfile $userProfile)
    {
        return (bool) $userProfile->gender && (bool) $userProfile->phone_number && (bool) $userProfile->occupation && self::getIsProfileAddressCompleted($userProfile);
    }

    public static function getIsProfileAddressCompleted(UserProfile $userProfile)
    {
        return (bool) $userProfile->city && (bool) $userProfile->country && (bool) $userProfile->address && (bool) $userProfile->postal_code;
    }

    public static function getIsMinor($userId)
    {
        $userProfile = UserProfile::select('date_of_birth')->where('user_id', $userId)->first();

        return $userProfile->date_of_birth > Carbon::now()->subYears(13)->format('Y-m-d');
    }

    // Update enable_salary_scale flag
    public function updateUserEnableSalaryScaleFlag($request)
    {
        // Authenticated User
        $authenticatedUser = auth()->user();

        $this->userProfile->where('user_id', $authenticatedUser->id)->update([
            'enable_salary_scale' => $request->enable_salary_scale,
        ]);

        return response()->json([
            'success' => true,
            'message' => Lang::get('general.salaryScaleFlag'),
        ]);
    }

    public function updateUserAddress($userId, $address, $city, $country, $postalCode)
    {
        return $this->userProfile
            ->where('user_id', $userId)
            ->update([
                'address' => $address,
                'city' => $city,
                'country' => $country,
                'postal_code' => $postalCode,
            ]);
    }
}
