<?php

namespace App\Transformers\IU;

use App\Models\UserProfile;
use League\Fractal\TransformerAbstract;

class IuProfileTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(UserProfile $userProfile)
    {
        return [
            'id' => $userProfile->id,
            'first_name' => $userProfile->first_name,
            'last_name' => $userProfile->last_name,
            'date_of_birth' => $userProfile->date_of_birth,
            'gender' => $userProfile->gender,
            'occupation' => $userProfile->occupation,
            'email' => $userProfile->email,
            'country' => $userProfile->country,
            'city' => $userProfile->city,
            'address' => $userProfile->address,
            'postal_code' => $userProfile->postal_code,
            'phone_number' => $userProfile->phone_number,
            'facebook_url' => $userProfile->facebook_url,
            'instagram_url' => $userProfile->instagram_url,
            'twitter_url' => $userProfile->twitter_url,
            'linkedin_url' => $userProfile->linkedin_url,
            'snapchat_url' => $userProfile->snapchat_url,
            'youtube_url' => $userProfile->youtube_url,
            'pinterest_url' => $userProfile->pinterest_url,
        ];
    }
}
