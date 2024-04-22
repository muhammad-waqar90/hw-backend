<?php

namespace Tests\Feature\IU\Profile;

use App\DataObject\IdentityVerificationStatusData;
use App\DataObject\Tests\SecondUserData;
use App\DataObject\Tests\UserData;
use App\Models\IdentityVerification;
use App\Models\User;
use App\Models\UserProfile;

use Illuminate\Support\Facades\Lang;
use Illuminate\Http\UploadedFile;

use Tests\TestCase;

use App\Traits\Tests\PermGroupUserTestTrait;

class ProfileTest extends TestCase
{
    use PermGroupUserTestTrait;

    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
        $this->actingAs($this->user);
    }

    public function testGetProfile()
    {
        UserProfile::factory()->withUser($this->user->id)->create();

        $response = $this->json('GET', '/api/iu/profile/me');

        $response->assertOk();
    }
    public function testUpdateProfileValid()
    {
        UserProfile::factory()->withUser($this->user->id)->create();
        IdentityVerification::factory()->withUser($this->user->id)->withStatus(IdentityVerificationStatusData::COMPLETED)->create();

        $response = $this->json('POST', '/api/iu/profile/me', array(
            'gender' => "M",
            'occupation' => "student_school",
            'country' => "Bosna",
            'city' => "Sarajevo",
            'address' => "Arebent",
            'postalCode' => "71000",
            'phoneNumber' => "56453411221",
            'facebookUrl' => "https://www.facebook.com/watch?v=gebh9g1TMRU",
            'instagramUrl' => "https://www.instagram.com/watch?v=gebh9g1TMRU",
            'twitterUrl' => "https://www.twitter.com/watch?v=gebh9g1TMRU",
            'linkedinUrl' => "https://www.linkedin.com/watch?v=gebh9g1TMRU",
            'snapchatUrl' => "https://www.snapchat.com/watch?v=gebh9g1TMRU",
            'youtubeUrl' => "https://www.youtube.com/watch?v=gebh9g1TMRU",
            'pinterestUrl' => "https://www.pinterest.com/watch?v=gebh9g1TMRU"
        ));

        $this->assertEquals(Lang::get('iu.profile.successUpdate'), json_decode($response->content())->message);
    }
    public function testYoutubeUrlUpdateProfileInvalid()
    {
        UserProfile::factory()->withUser($this->user->id)->create();

        $response = $this->json('POST', '/api/iu/profile/me', array(
            'gender' => "M",
            'occupation' => "student_school",
            'country' => "Bosna",
            'city' => "Sarajevo",
            'address' => "Arebent",
            'postalCode' => "71000",
            'phoneNumber' => "56453411221",
            'facebookUrl' => "https://www.facebook.com/watch?v=gebh9g1TMRU",
            'instagramUrl' => "https://www.instagram.com/watch?v=gebh9g1TMRU",
            'twitterUrl' => "https://www.twitter.com/watch?v=gebh9g1TMRU",
            'linkedinUrl' => "https://www.linkedin.com/watch?v=gebh9g1TMRU",
            'snapchatUrl' => "https://www.snapchat.com/watch?v=gebh9g1TMRU",
            'youtubeUrl' => "https://www.hijaz-world.com/watch?v=gebh9g1TMRU",
            'pinterestUrl' => "https://www.pinterest.com/watch?v=gebh9g1TMRU"
        ));

        $this->assertEquals('The youtube url format is invalid.', json_decode($response->content())->message);
    }

    public function testGetIdentityRouteWhenNotUpdated()
    {
        UserProfile::factory()->withUser($this->user->id)->create();

        $response = $this->json('GET', '/api/iu/profile/identity');

        $this->assertEquals(false, json_decode($response["verified"]));
        $response->assertOk();
    }

    public function testGetIdentityRouteWhenUpdated()
    {
        UserProfile::factory()->withUser($this->user->id)->create();
        IdentityVerification::factory()->withUser($this->user->id)->create();
        $response = $this->json('GET', '/api/iu/profile/identity');

        $this->assertEquals(true, json_decode($response["verified"]));
        $response->assertOk();
    }

    public function testPostIdentityRouteWhenNotUpdated()
    {
        UserProfile::factory()->withUser($this->user->id)->create();

        $response = $this->json('POST', '/api/iu/profile/identity', [
            'identityFile' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $response->assertOk();

        $response = $this->json('GET', '/api/iu/profile/identity');

        $this->assertEquals(true, json_decode($response["verified"]));
    }

    public function testDeleteUser()
    {
        UserProfile::factory()->withUser($this->user->id)->create();
        
        $this->json('POST',  '/api/auth/login', [
            'username' => $this->user->name,
            'password' => UserData::PASSWORD,
        ]);

        $response = $this->json('DELETE', '/api/iu/me', [
            'feedback' => "I did not found the courses interested"
        ]);
        $response->assertOk();
    }

    public function testChangePasswordValid()
    {
        $response = $this->json('PUT', '/api/iu/change-password',  array(
            'current_password' => UserData::PASSWORD,
            'password' => SecondUserData::PASSWORD,
            'password_confirmation' => SecondUserData::PASSWORD
        ));
        
        $response->assertOk();
    }

    public function testChangePasswordInvalid()
    {
        $response = $this->json('PUT', '/api/iu/change-password',  array(
            'current_password' => UserData::PASSWORD,
            'password' => UserData::PASSWORD,
            'password_confirmation' => UserData::PASSWORD
        ));
        
        $response->assertStatus(422);
    }
}
