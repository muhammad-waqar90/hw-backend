<?php

namespace App\Repositories;

use App\Models\PasswordReset;
use App\Models\User;
use App\Models\VerifyUser;
use App\Models\VerifyUserAge;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AuthenticationRepository
{

    private User $user;
    private VerifyUser $verifyUser;
    private PasswordReset $passwordReset;
    private VerifyUserAge $verifyUserAge;

    public function __construct(User $user, VerifyUser $verifyUser, PasswordReset $passwordReset,
        VerifyUserAge $verifyUserAge)
    {
        $this->user = $user;
        $this->verifyUser = $verifyUser;
        $this->passwordReset = $passwordReset;
        $this->verifyUserAge = $verifyUserAge;
    }

    /**
     * @param User $user
     * @return VerifyUser
     */
    public function createVerifyUser(User $user)
    {
        return $this->verifyUser->updateOrCreate([
            'user_id'   => $user->id,
            ],
            [
                'token'     => Str::random(20)
            ]
        );
    }

    public function createVerifyUserAge($userId)
    {
        return $this->verifyUserAge->updateOrCreate([
                'user_id'   => $userId
            ],
            [
                'token'     => Str::random(20)
            ]
        );
    }

    public function validateUser($token)
    {
        $verifyUser = $this->verifyUser->where('token', $token)->first();
        if(!$verifyUser)
            throw new \Exception();

        $user = $verifyUser->user;
        $user->email_verified_at = Carbon::now();

        $user->save();
        $verifyUser->delete();
        return $user;
    }

    public function validateUserAge($token)
    {
        $verifyUserAge = $this->verifyUserAge->where('token', $token)->first();
        if(!$verifyUserAge)
            throw new \Exception();

        $user = $verifyUserAge->user;
        $user->age_verified_at = Carbon::now();

        $user->save();
        $verifyUserAge->delete();
        return $user;
    }

    public function onVerificationExpire(VerifyUser $verifyUser)
    {
        $verifyUser->delete();
        return true;
    }

    public function onAgeVerificationExpire(VerifyUserAge $verifyUserAge)
    {
        return $verifyUserAge->delete();
    }

    public function checkIfEmailExists($email)
    {
        return $this->user->where('email', $email)->exists();
    }

    public function checkIfUsernameExists($username)
    {
        return $this->user->where('name', $username)->exists();
    }

    public function createPasswordResetToken($userName)
    {
        return $this->passwordReset->updateOrCreate([
            'name'     => $userName,
            ],
            [
            'token'     => Str::random(20)
            ]);
    }

    public function checkIfPasswordResetTokenExists($token)
    {
        return $this->passwordReset->where('token', $token)->exists();
    }

    public function getPasswordReset($token)
    {
        return $this->passwordReset->where('token', $token)->first();
    }

    public function updateUserPassword($name, $password)
    {
        return $this->user->where('name', $name)->update([
            'password'      => bcrypt($password)
        ]);
    }

    public function deletePasswordReset($id)
    {
        return $this->passwordReset->where('id', $id)->delete();
    }

    public function generateUsername($firstName, $lastName)
    {
        $firstName = strtolower(preg_replace("/[^a-zA-Z]+/", "", $firstName));
        $lastName = strtolower(preg_replace("/[^a-zA-Z]+/", "", $lastName));
        $username = substr($firstName, 0, 2) . '_' . $lastName;
        $similarUsername = $this->checkIfSimilarUsernameExists($username);

        if(!$similarUsername)
            return $username . '1';

        $index = preg_replace('/[^0-9]/', '', $similarUsername->name);
        return $username . ++$index;
    }

    public function findVerifyUserAgeById($userId)
    {
        return $this->verifyUserAge->where('user_id', $userId)->first();
    }

    private function checkIfSimilarUsernameExists($username)
    {
        return $this->user->where('name', 'regexp', $username . '[0-9]+')->latest()->first();
    }
}
