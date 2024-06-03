<?php

namespace Database\Factories;

use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $users_id = DB::table('users')->pluck('id');

        return [
            'user_id' => $users_id->random(),
            'email' => Str::random(10).'@test.com',
            'date_of_birth' => fake()->date,
            'gender' => 'M',
            'occupation' => 'legal',
            'country' => 'Test',
            'city' => fake()->city,
            'address' => fake()->address,
            'postal_code' => fake()->postCode,
            'phone_number' => fake()->phoneNumber,
            'facebook_url' => 'facebook.com/'.Str::random(8),
            'instagram_url' => 'instagram.com/'.Str::random(8),
            'twitter_url' => 'twitter.com/'.Str::random(8),
            'linkedin_url' => 'linkedin.com/'.Str::random(8),
            'snapchat_url' => 'snapchat.com/'.Str::random(8),
            'youtube_url' => 'youtube.com/'.Str::random(8),
            'pinterest_url' => 'pinterest.com/'.Str::random(8),
        ];
    }

    /**
     * Indicate that the user is suspended.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withUser($userId)
    {
        return $this->state(fn () => [
            'user_id' => $userId,
        ]);
    }

    public function withEmail($email)
    {
        return $this->state(fn () => [
            'email' => $email,
        ]);
    }

    public function withFacebookUrl($url)
    {
        return $this->state(fn () => [
            'facebook_url' => $url,
        ]);
    }

    public function withYoutubeUrl($url)
    {
        return $this->state(fn () => [
            'youtube_url' => $url,
        ]);
    }
}
