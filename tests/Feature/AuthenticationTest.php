<?php

namespace Tests\Feature;

use App\DataObject\Tests\SecondUserData;
use App\DataObject\Tests\UserData;
use App\Models\PasswordHistory;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public $userStructure = [
        'id',
        'role_id',
        'name',
        'first_name',
        'last_name',
        'email_verified_at',
        'created_at',
        'updated_at',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');
    }

    public function testRegisterValid()
    {
        $response = $this->json('POST', '/api/auth/register', [
            'first_name' => UserData::FIRST_NAME,
            'last_name' => UserData::LAST_NAME,
            'email' => UserData::EMAIL,
            'password' => UserData::PASSWORD,
            'password_confirmation' => UserData::PASSWORD,
            'dateOfBirth' => UserData::DATE_OF_BIRTH,
            'captchaToken' => '20000000-aaaa-bbbb-cccc-000000000002',
            'communicationAccepted' => true,
            'termsAndConditionsAccepted' => true,
        ]);

        $response->assertStatus(200);
    }

    public function testFullRegisterValid()
    {
        $response = $this->json('POST', '/api/auth/register', [
            'first_name' => UserData::FIRST_NAME,
            'last_name' => UserData::LAST_NAME,
            'email' => UserData::EMAIL,
            'password' => UserData::PASSWORD,
            'password_confirmation' => UserData::PASSWORD,
            'dateOfBirth' => UserData::DATE_OF_BIRTH,
            'captchaToken' => '20000000-aaaa-bbbb-cccc-000000000002',
            'communicationAccepted' => true,
            'termsAndConditionsAccepted' => true,
        ]);

        $response->assertStatus(200);

        $user = DB::table('users')->where('first_name', UserData::FIRST_NAME)->first();
        $verifyUser = DB::table('verify_users')->where('user_id', $user->id)->first();

        $response = $this->json('POST', '/api/auth/verify', [
            'token' => $verifyUser->token,
        ]);

        $response->assertStatus(200);

        $response = $this->json('POST', '/api/auth/login', [
            'username' => $user->name,
            'password' => UserData::PASSWORD,
        ]);

        $response->assertStatus(200);
    }

    public function testRegisterValidTwoUsersSameNameDifferentUsername()
    {
        $response = $this->json('POST', '/api/auth/register', [
            'first_name' => UserData::FIRST_NAME,
            'last_name' => UserData::LAST_NAME,
            'email' => UserData::EMAIL,
            'password' => UserData::PASSWORD,
            'password_confirmation' => UserData::PASSWORD,
            'dateOfBirth' => UserData::DATE_OF_BIRTH,
            'captchaToken' => '20000000-aaaa-bbbb-cccc-000000000002',
            'communicationAccepted' => true,
            'termsAndConditionsAccepted' => true,
        ]);

        $response->assertStatus(200);

        $response = $this->json('POST', '/api/auth/register', [
            'first_name' => SecondUserData::FIRST_NAME,
            'last_name' => SecondUserData::LAST_NAME,
            'email' => SecondUserData::EMAIL,
            'password' => UserData::PASSWORD,
            'password_confirmation' => UserData::PASSWORD,
            'dateOfBirth' => UserData::DATE_OF_BIRTH,
            'captchaToken' => '20000000-aaaa-bbbb-cccc-000000000002',
            'communicationAccepted' => true,
            'termsAndConditionsAccepted' => true,
        ]);

        $response->assertStatus(200);

        $user1 = DB::table('users')->where('first_name', UserData::FIRST_NAME)->first();
        $user2 = DB::table('users')->where('first_name', SecondUserData::FIRST_NAME)->first();

        $this->assertTrue($user1->name != $user2->name);
    }

    public function testRegisterInvalidWrongCredentials()
    {
        $response = $this->json('POST', '/api/auth/register', [
            'first_name' => UserData::FIRST_NAME,
            'last_name' => UserData::LAST_NAME,
            'email' => UserData::EMAIL,
            'password' => 'a',
            'password_confirmation' => 'a',
            'captchaToken' => '20000000-aaaa-bbbb-cccc-000000000002',
            'communicationAccepted' => true,
            'termsAndConditionsAccepted' => true,
        ]);

        $response->assertStatus(422);
    }

    public function testRegisterInvalidTwoUsersSameEmail()
    {
        $response = $this->json('POST', '/api/auth/register', [
            'first_name' => UserData::FIRST_NAME,
            'last_name' => UserData::LAST_NAME,
            'email' => UserData::EMAIL,
            'password' => UserData::PASSWORD,
            'password_confirmation' => UserData::PASSWORD,
            'dateOfBirth' => UserData::DATE_OF_BIRTH,
            'captchaToken' => '20000000-aaaa-bbbb-cccc-000000000002',
            'communicationAccepted' => true,
            'termsAndConditionsAccepted' => true,
        ]);

        $response->assertStatus(200);

        $response = $this->json('POST', '/api/auth/register', [
            'first_name' => SecondUserData::FIRST_NAME,
            'last_name' => SecondUserData::LAST_NAME,
            'email' => UserData::EMAIL,
            'password' => SecondUserData::PASSWORD,
            'password_confirmation' => SecondUserData::PASSWORD,
            'dateOfBirth' => UserData::DATE_OF_BIRTH,
            'captchaToken' => '20000000-aaaa-bbbb-cccc-000000000002',
            'communicationAccepted' => true,
            'termsAndConditionsAccepted' => true,
        ]);

        $response->assertStatus(422);
    }

    public function testWebLoginValid()
    {
        $verifiedUser = User::factory()->verified()->create();

        $response = $this->json('POST', '/api/auth/login', [
            'username' => $verifiedUser->name,
            'password' => UserData::PASSWORD,
        ]);

        $response->assertStatus(200);
    }

    public function testWebLoginInvalidWrongCredentials()
    {
        $verifiedUser = User::factory()->verified()->create();

        $response = $this->json('POST', '/api/auth/login', [
            'username' => $verifiedUser->name,
            'password' => SecondUserData::PASSWORD,
        ]);

        $response->assertStatus(401);
    }

    public function testWebLoginInvalidNotRegistered()
    {
        $response = $this->json('POST', '/api/auth/login', [
            'username' => UserData::EMAIL,
            'password' => UserData::PASSWORD,
        ]);

        $response->assertStatus(401);
    }

    public function testWebLoginInvalidNotVerified()
    {
        $user = User::factory()->create();

        $response = $this->json('POST', '/api/auth/login', [
            'username' => $user->name,
            'password' => UserData::PASSWORD,
        ]);

        $response->assertStatus(401);
    }

    public function testMobileLoginValid()
    {
        $verifiedUser = User::factory()->verified()->create();

        $response = $this->json('POST', '/api/auth/mobile/login', [
            'username' => $verifiedUser->name,
            'password' => UserData::PASSWORD,
        ]);

        $response->assertStatus(200);
    }

    public function testMobileLoginInvalidWrongCredentials()
    {
        $verifiedUser = User::factory()->verified()->create();

        $response = $this->json('POST', '/api/auth/mobile/login', [
            'username' => $verifiedUser->name,
            'password' => SecondUserData::PASSWORD,
        ]);

        $response->assertStatus(401);
    }

    public function testMobileLoginInvalidNotRegistered()
    {
        $response = $this->json('POST', '/api/auth/mobile/login', [
            'username' => UserData::EMAIL,
            'password' => UserData::PASSWORD,
        ]);

        $response->assertStatus(401);
    }

    public function testMobileLoginInvalidNotVerified()
    {
        $user = User::factory()->create();

        $response = $this->json('POST', '/api/auth/mobile/login', [
            'username' => $user->name,
            'password' => UserData::PASSWORD,
        ]);

        $response->assertStatus(401);
    }

    public function testRequestPasswordResetValid()
    {
        $verifiedUser = User::factory()->verified()->create();

        UserProfile::factory()->withUser($verifiedUser->id)->create();
        $response = $this->json('POST', '/api/auth/password-reset/request', [
            'username' => $verifiedUser->name,
        ]);

        $response->assertStatus(200);
    }

    public function testRequestPasswordResetInvalidUsername()
    {
        $response = $this->json('POST', '/api/auth/password-reset/request', [
            'username' => 'nepostojim@',
        ]);

        $this->assertEquals(Lang::get('auth.usernameNotFound'), json_decode($response->content())->message);
        $response->assertStatus(200);
    }

    public function testResendParentVerificationValid()
    {
        $response = $this->json('POST', '/api/auth/register', [
            'first_name' => UserData::FIRST_NAME,
            'last_name' => UserData::LAST_NAME,
            'email' => UserData::EMAIL,
            'password' => UserData::PASSWORD,
            'password_confirmation' => UserData::PASSWORD,
            'dateOfBirth' => date('Y-m-d', strtotime('-1 years')),
            'parentEmailAddress' => 'parent@test.com',
            'captchaToken' => '20000000-aaaa-bbbb-cccc-000000000002',
            'communicationAccepted' => true,
            'termsAndConditionsAccepted' => true,
        ]);

        $response->assertStatus(200);

        $user = DB::table('users')->where('first_name', UserData::FIRST_NAME)->first();

        $response = $this->json('POST', '/api/auth/verify/parent/resend', [
            'parentEmailAddress' => 'testemail@test.com',
            'username' => $user->name,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(Lang::get('auth.parentVerificationCodeSent'), json_decode($response->content())->message);
    }

    public function testResendVerificationValid()
    {
        $response = $this->json('POST', '/api/auth/register', [
            'first_name' => UserData::FIRST_NAME,
            'last_name' => UserData::LAST_NAME,
            'email' => UserData::EMAIL,
            'password' => UserData::PASSWORD,
            'password_confirmation' => UserData::PASSWORD,
            'dateOfBirth' => UserData::DATE_OF_BIRTH,
            'captchaToken' => '20000000-aaaa-bbbb-cccc-000000000002',
            'communicationAccepted' => true,
            'termsAndConditionsAccepted' => true,
        ]);

        $response->assertStatus(200);

        $user = DB::table('users')->where('first_name', UserData::FIRST_NAME)->first();

        $response = $this->json('POST', '/api/auth/verify/resend', [
            'username' => $user->name,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(Lang::get('auth.verificationCodeSent'), json_decode($response->content())->message);
    }

    public function testAgeVerificationValid()
    {
        $response = $this->json('POST', '/api/auth/register', [
            'first_name' => UserData::FIRST_NAME,
            'last_name' => UserData::LAST_NAME,
            'email' => UserData::EMAIL,
            'password' => UserData::PASSWORD,
            'password_confirmation' => UserData::PASSWORD,
            'dateOfBirth' => date('Y-m-d', strtotime('-1 years')),
            'parentEmailAddress' => 'parent@test.com',
            'captchaToken' => '20000000-aaaa-bbbb-cccc-000000000002',
            'communicationAccepted' => true,
            'termsAndConditionsAccepted' => true,
        ]);

        $response->assertStatus(200);

        $user = DB::table('users')->where('first_name', UserData::FIRST_NAME)->first();
        $verifyUserAges = DB::table('verify_user_ages')->where('user_id', $user->id)->first();

        $response = $this->json('POST', '/api/auth/verify-age', [
            'token' => $verifyUserAges->token,
        ]);

        $response->assertStatus(200);
    }

    public function testAgeVerificationInvalidToken()
    {
        $response = $this->json('POST', '/api/auth/verify-age', [
            'token' => '11111111111111111111',
        ]);

        $response->assertStatus(401);
    }

    public function testVerificationValid()
    {
        $response = $this->json('POST', '/api/auth/register', [
            'first_name' => UserData::FIRST_NAME,
            'last_name' => UserData::LAST_NAME,
            'email' => UserData::EMAIL,
            'password' => UserData::PASSWORD,
            'password_confirmation' => UserData::PASSWORD,
            'dateOfBirth' => UserData::DATE_OF_BIRTH,
            'captchaToken' => '20000000-aaaa-bbbb-cccc-000000000002',
            'communicationAccepted' => true,
            'termsAndConditionsAccepted' => true,
        ]);

        $response->assertStatus(200);

        $user = DB::table('users')->where('first_name', UserData::FIRST_NAME)->first();
        $verifyUser = DB::table('verify_users')->where('user_id', $user->id)->first();

        $response = $this->json('POST', '/api/auth/verify', [
            'token' => $verifyUser->token,
        ]);

        $response->assertStatus(200);
    }

    public function testVerificationInvalidToken()
    {
        $response = $this->json('POST', '/api/auth/verify', [
            'token' => '11111111111111111111',
        ]);

        $response->assertStatus(401);
    }

    public function testPasswordResetValid()
    {
        $verifiedUser = User::factory()->verified()->create();
        UserProfile::factory()->withUser($verifiedUser->id)->create();

        $response = $this->json('POST', '/api/auth/password-reset/request', [
            'username' => $verifiedUser->name,
        ]);

        $response->assertStatus(200);

        $passwordReset = DB::table('password_resets')->where('name', $verifiedUser->name)->first();

        $response = $this->json('POST', '/api/auth/password-reset/check', [
            'token' => $passwordReset->token,
        ]);

        $response->assertStatus(200);

        $response = $this->json('PUT', '/api/auth/password-reset', [
            'token' => $passwordReset->token,
            'password' => SecondUserData::PASSWORD,
            'password_confirmation' => SecondUserData::PASSWORD,
        ]);

        $response->assertStatus(200);

        $response = $this->json('POST', '/api/auth/login', [
            'username' => $verifiedUser->name,
            'password' => SecondUserData::PASSWORD,
        ]);

        $response->assertStatus(200);
    }

    public function testPasswordResetValidTwoRequestsDifferentTokens()
    {
        $verifiedUser = User::factory()->verified()->create();
        UserProfile::factory()->withUser($verifiedUser->id)->create();

        $response = $this->json('POST', '/api/auth/password-reset/request', [
            'username' => $verifiedUser->name,
        ]);

        $response->assertStatus(200);

        $passwordReset = DB::table('password_resets')->where('name', $verifiedUser->name)->first();

        $response = $this->json('POST', '/api/auth/password-reset/check', [
            'token' => $passwordReset->token,
        ]);

        $response->assertStatus(200);

        $response = $this->json('POST', '/api/auth/password-reset/request', [
            'username' => $verifiedUser->name,
        ]);

        $response->assertStatus(200);

        $response = $this->json('POST', '/api/auth/password-reset/check', [
            'token' => $passwordReset->token,
        ]);

        $response->assertStatus(400);

        $passwordReset = DB::table('password_resets')->where('name', $verifiedUser->name)->first();

        $response = $this->json('PUT', '/api/auth/password-reset', [
            'token' => $passwordReset->token,
            'password' => SecondUserData::PASSWORD,
            'password_confirmation' => SecondUserData::PASSWORD,
        ]);

        $response->assertStatus(200);
    }

    public function testPasswordResetInvalidPassword()
    {
        $verifiedUser = User::factory()->verified()->create();
        UserProfile::factory()->withUser($verifiedUser->id)->create();

        $response = $this->json('POST', '/api/auth/password-reset/request', [
            'username' => $verifiedUser->name,
        ]);

        $response->assertStatus(200);

        $passwordReset = DB::table('password_resets')->where('name', $verifiedUser->name)->first();

        $response = $this->json('POST', '/api/auth/password-reset/check', [
            'token' => $passwordReset->token,
        ]);

        $response->assertStatus(200);

        $response = $this->json('PUT', '/api/auth/password-reset', [
            'token' => $passwordReset->token,
            'password' => 'p',
            'password_confirmation' => 'p',
        ]);

        $response->assertStatus(422);
    }

    public function testRequestPasswordResetNotVerified()
    {
        $user = User::factory()->create();
        UserProfile::factory()->withUser($user->id)->create();

        $response = $this->json('POST', '/api/auth/password-reset/request', [
            'username' => $user->name,
        ]);

        $response->assertStatus(200);
    }

    public function testPasswordResetInvalidToken()
    {
        $verifiedUser = User::factory()->verified()->create();

        $response = $this->json('PUT', '/api/auth/password-reset', [
            'token' => '11111111111111111111',
            'password' => SecondUserData::PASSWORD,
            'password_confirmation' => SecondUserData::PASSWORD,
        ]);

        $response->assertStatus(400);

        $response = $this->json('POST', '/api/auth/login', [
            'username' => $verifiedUser->name,
            'password' => SecondUserData::PASSWORD,
        ]);

        $response->assertStatus(401);

        $response = $this->json('POST', '/api/auth/login', [
            'username' => $verifiedUser->name,
            'password' => UserData::PASSWORD,
        ]);

        $response->assertStatus(200);
    }

    public function testPasswordResetInvalidSamePassword()
    {
        $verifiedUser = User::factory()->verified()->create();
        UserProfile::factory()->withUser($verifiedUser->id)->create();
        PasswordHistory::query()->create([
            'user_id' => $verifiedUser->id,
            'password' => bcrypt(UserData::PASSWORD),
        ]);

        $response = $this->json('POST', '/api/auth/password-reset/request', [
            'username' => $verifiedUser->name,
        ]);

        $response->assertStatus(200);

        $passwordReset = DB::table('password_resets')->where('name', $verifiedUser->name)->first();

        $response = $this->json('PUT', '/api/auth/password-reset', [
            'token' => $passwordReset->token,
            'password' => UserData::PASSWORD,
            'password_confirmation' => UserData::PASSWORD,
        ]);

        $response->assertStatus(422);

        $response = $this->json('POST', '/api/auth/login', [
            'username' => $verifiedUser->name,
            'password' => UserData::PASSWORD,
        ]);

        $response->assertStatus(200);
    }

    public function testMeValid()
    {
        $verifiedUser = User::factory()->verified()->create();

        $response = $this->actingAs($verifiedUser)->json('GET', '/api/auth/me');
        $response->assertStatus(200);
    }

    public function testMeInvalid()
    {
        $response = $this->json('GET', '/api/auth/me');
        $response->assertUnauthorized();
    }

    public function testLogoutValid()
    {
        $verifiedUser = User::factory()->verified()->create();

        $response = $this->json('POST', '/api/auth/login', [
            'username' => $verifiedUser->name,
            'password' => UserData::PASSWORD,
        ]);

        $response->assertStatus(200);

        $response = $this->json('GET', '/api/auth/me');
        $response->assertOk();
        $response->assertJsonStructure($this->userStructure);

        $response = $this->json('POST', '/api/auth/logout', [
            'username' => $verifiedUser->name,
        ]);

        $response->assertStatus(200);

        $response = $this->json('GET', '/api/auth/me');
        $response->assertUnauthorized();
    }

    public function testLogoutInvalid()
    {
        $verifiedUser = User::factory()->verified()->create();

        $response = $this->json('POST', '/api/auth/logout', [
            'email' => $verifiedUser->email,
        ]);

        $response->assertUnauthorized();
    }

    public function testRefresh()
    {
        $verifiedUser = User::factory()->verified()->create();
        $token = auth()->fromUser($verifiedUser);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)->json('GET', '/api/auth/me');
        $response->assertStatus(200);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)->json('POST', '/api/auth/refresh');
        $response->assertStatus(200);
        $newToken = $response->getData()->access_token;

        //check that you are authenticated with the new token
        $response = $this->withHeader('Authorization', 'Bearer '.$newToken)->json('GET', '/api/auth/me');
        $response->assertStatus(200);
    }

    // AGE VERIFICATION

    public function testRegister10YearOldValid()
    {
        $tenYearOld = Carbon::now()->subYears(10)->format('y-m-d');

        $response = $this->json('POST', '/api/auth/register', [
            'first_name' => UserData::FIRST_NAME,
            'last_name' => UserData::LAST_NAME,
            'email' => UserData::EMAIL,
            'password' => UserData::PASSWORD,
            'password_confirmation' => UserData::PASSWORD,
            'dateOfBirth' => $tenYearOld,
            'parentEmailAddress' => 'parent@test.com',
            'captchaToken' => '20000000-aaaa-bbbb-cccc-000000000002',
            'communicationAccepted' => true,
            'termsAndConditionsAccepted' => true,
        ]);

        $this->assertEquals(Lang::get('auth.successfullyCreatedAccount'), json_decode($response->content())->message);
    }

    public function testRegister10YearOldInvalidNoParentEmailAddress()
    {
        //register passes if age is formatted correctly
        $tenYearOld = Carbon::now()->subYears(10);

        $response = $this->json('POST', '/api/auth/register', [
            'first_name' => UserData::FIRST_NAME,
            'last_name' => UserData::LAST_NAME,
            'email' => UserData::EMAIL,
            'password' => UserData::PASSWORD,
            'password_confirmation' => UserData::PASSWORD,
            'dateOfBirth' => $tenYearOld,
            'captchaToken' => '20000000-aaaa-bbbb-cccc-000000000002',
            'communicationAccepted' => true,
            'termsAndConditionsAccepted' => true,
        ]);

        $this->assertEquals("Legal guardian's email address field is required.", json_decode($response->content())->errors->parentEmailAddress[0]);
    }

    public function testForgotUsernameValid()
    {
        $user = User::factory()->verified()->create();
        UserProfile::factory()->withUser($user->id)->withEmail(UserData::EMAIL)->create();

        $response = $this->json('POST', '/api/auth/username/forgot/request', [
            'email' => UserData::EMAIL,
        ]);

        $response->assertStatus(200);
    }

    public function testRestoreUserValid()
    {
        $user = User::factory()->verified()->create();
        DB::table('restore_users')->insert(['user_id' => $user->id, 'token' => Str::random(40), 'created_at' => Date::now(), 'updated_at' => Date::now()]);
        $restoreUser = DB::table('restore_users')->where('user_id', $user->id)->first();

        $response = $this->json('POST', '/api/auth/restore', [
            'token' => $restoreUser->token,
        ]);

        $response->assertStatus(200);
    }
}
