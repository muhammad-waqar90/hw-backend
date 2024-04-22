<?php

namespace App\Http\Controllers;

use App\DataObject\FeedbackTypeData;
use App\DataObject\RoleData;
use App\DataObject\Tickets\TicketCategoryData;
use App\Events\Tickets\IuAccountRestored;
use App\Events\Tickets\IuAccountTrashed;
use App\Events\Users\IuUserCreated;
use App\Events\Users\UserPasswordUpdated;
use App\Exceptions\Auth\PendingAgeVerificationException;
use App\Exceptions\Auth\PendingEmailVerificationException;
use App\Exceptions\Auth\RestoreUserException;
use App\Http\Requests\Auth\CheckPasswordResetTokenRequest;
use App\Http\Requests\Auth\CreateUserRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RequestForgotUsernameRequest;
use App\Http\Requests\Auth\RequestPasswordResetRequest;
use App\Http\Requests\Auth\ResendVerificationCodeRequest;
use App\Http\Requests\Auth\RestoreUserRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Requests\IU\IuChangePasswordRequest;
use App\Http\Requests\IU\IuFeedbackRequest;
use App\Http\Requests\ResendParentVerificationCodeRequest;
use App\Mail\AgeVerificationEmail;
use App\Mail\ForgotUsernameEmail;
use App\Mail\IU\Account\IuAccountTrashedEmail;
use App\Mail\PasswordResetEmail;
use App\Mail\VerificationEmail;
use App\Models\User;
use App\Repositories\AuthenticationRepository;
use App\Repositories\HA\PermissionRepository;
use App\Repositories\IU\IuUserRepository;
use App\Repositories\PasswordHistoryRepository;
use App\Repositories\TicketRepository;
use App\Services\HCaptcha\HCaptchaService;
use App\Traits\UtilsTrait;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    use AuthenticatesUsers, UtilsTrait;

    private IuUserRepository $iuUserRepository;
    private AuthenticationRepository $authRepository;
    private PermissionRepository $permissionRepository;
    private TicketRepository $ticketRepository;
    private PasswordHistoryRepository $passwordHistoryRepository;

    public function __construct(
        IuUserRepository $iuUserRepository,
        AuthenticationRepository $authRepository,
        PermissionRepository $permissionRepository,
        TicketRepository $ticketRepository,
        PasswordHistoryRepository $passwordHistoryRepository
    ) {
        $this->iuUserRepository = $iuUserRepository;
        $this->authRepository = $authRepository;
        $this->permissionRepository = $permissionRepository;
        $this->ticketRepository = $ticketRepository;
        $this->passwordHistoryRepository = $passwordHistoryRepository;
        // field name which required to use for authenticates users
        $this->username = 'name';
    }

    public function register(CreateUserRequest $request)
    {
        try {
            //TODO: HCaptchaService::verify is not working in custom validation rule
            $captcha = HCaptchaService::verify($request->captchaToken);
            if (!$captcha)
                return response()->json(['errors' => Lang::get('auth.invalidCaptcha')], 401);

            $username = $this->authRepository->generateUsername($request->first_name, $request->last_name);
            $isMinor = $this->isMinor($request->dateOfBirth);
            $user = $this->iuUserRepository->create(
                $username,
                $request->first_name,
                $request->last_name,
                $request->password,
                $request->communicationAccepted,
                $isMinor
            );

            IuUserCreated::dispatch(
                $user,
                $request->email,
                $request->dateOfBirth,
                $request->password,
                $isMinor ? $request->parentEmailAddress : null
            );

            return response()->json([
                'message' => Lang::get('auth.successfullyCreatedAccount'),
                'username' => $username
            ], 200);
        } catch (\Exception $e) {
            Log::error('Exception: AuthController@register', [$e->getMessage()]);
            if ($e->getCode() == 23000)
                return response()->json(['errors' => Lang::get('general.wentWrong')], 400);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Web Login
    |--------------------------------------------------------------------------
    |
    | this function is used specially for those mediums where we are setting a
    | limited i.e 1 hour expiry token after which user required to re-authenticate
    |
    */
    public function webLogin(LoginRequest $request)
    {
        try {
            $token = $this->login($request->username, $request->password);

            return $this->respondWithToken($token);
        } catch (AuthenticationException $e) {
            return response()->json(['errors' => $e->getMessage()], 401);
        } catch (PendingEmailVerificationException $e) {
            return response()->json($e->getErrors(), 401);
        } catch (PendingAgeVerificationException $e) {
            return response()->json($e->getErrors(), 401);
        } catch (RestoreUserException $e) {
            return response()->json($e->getErrors(), 406);
        } catch (\Exception $e) {
            Log::error('Exception: AuthController@webLogin', [$e->getMessage()]);
            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Mobile Login
    |--------------------------------------------------------------------------
    |
    | Any mobile/handheld device
    |
    | this function is used those mediums where we are setting a longer expiry
    | of the token i.e 1 year expiry token e.g: mobile app
    |
    */
    public function mobileLogin(LoginRequest $request)
    {
        try {
            $this->setTokenExpiry(config('jwt.custom_ttl.year'));

            $token = $this->login($request->username, $request->password);
            return $this->respondWithToken($token);
        } catch (AuthenticationException $e) {
            return response()->json(['errors' => $e->getMessage()], 401);
        } catch (PendingEmailVerificationException $e) {
            return response()->json($e->getErrors(), 401);
        } catch (PendingAgeVerificationException $e) {
            return response()->json($e->getErrors(), 401);
        } catch (RestoreUserException $e) {
            return response()->json($e->getErrors(), 406); // 406 Not Acceptable
        } catch (\Exception $e) {
            Log::error('Exception: AuthController@webLogin', [$e->getMessage()]);
            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    private function login($name, $password)
    {
        $credentials = [
            'name' => $name,
            'password' => $password
        ];

        if (!$token = auth()->attempt($credentials))
            throw new AuthenticationException(Lang::get('auth.invalidUsernameOrPassword'));

        $user = auth()->user();
        if (!$user->email_verified_at)
            throw new PendingEmailVerificationException();
        if (!$user->age_verified_at)
            throw new PendingAgeVerificationException($user->name);
        if ($user->restoreUser)
            throw new RestoreUserException($user->restoreUser->token);
        if (!$user->is_enabled)
            throw new AuthenticationException(Lang::get('auth.accountDisabled'));

        $this->iuUserRepository->updateUserLastActive($user->id);

        return $token;
    }

    public function me()
    {
        $user = auth()->user();
        if ($user->role_id === RoleData::ADMIN)
            $user->permissions = $this->permissionRepository->getUserPermissionIds($user->id);
        if ($user->role_id === RoleData::INDEPENDENT_USER)
            $user->load('userProfile', 'salaryScale', 'salaryScale.discountedCountry', 'salaryScale.discountedCountryRange');

        return response()->json(auth()->user(), 200);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => Lang::get('auth.successfullyLoggedOut')]);
    }

    public function refresh()
    {
        try {
            $refreshedToken = auth()->refresh();
            $user = auth()->setToken($refreshedToken)->user();

            if ($user && !$user->is_enabled)
                return response()->json(['errors' => Lang::get('auth.accountDisabled')], 401);

            $this->iuUserRepository->updateUserLastActive($user->id);
            return $this->respondWithToken($refreshedToken);
        } catch (\Exception $e) {
            Log::error('Exception: AuthController@refresh', [$e->getMessage()]);
            return response()->json(['errors' => Lang::get('auth.unauthenticated')], 401);
        }
    }

    public function verify(VerifyEmailRequest $request)
    {
        try {
            $this->authRepository->validateUser($request->token);

            return response()->json(['message' => Lang::get('auth.verificationSuccess')]);
        } catch (\Exception $e) {
            Log::error('Exception: AuthController@verify', [$e->getMessage()]);
            return response()->json(['errors' => Lang::get('auth.verificationCodeInvalid')], 401);
        }
    }

    public function verifyAge(VerifyEmailRequest $request)
    {
        try {
            $this->authRepository->validateUserAge($request->token);

            return response()->json(['message' => Lang::get('auth.verificationSuccess')]);
        } catch (\Exception $e) {
            Log::error('Exception: AuthController@verifyAge', [$e->getMessage()]);
            return response()->json(['errors' => Lang::get('auth.verificationCodeInvalid')], 401);
        }
    }

    public function delete(IuFeedbackRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = $this->iuUserRepository->getUser($request->user()->id);
            if (!$user)
                return response()->json(['errors' => Lang::get('general.notFound')], 404);

            if ($user->restoreUser)
                return response()->json(['message' => Lang::get('auth.accountTrashed')], 200);

            $exists = $this->ticketRepository->checkIfAnyUnResolvedTicketsExist($user->id, [TicketCategoryData::REFUND]);
            if ($exists)
                return response()->json(['errors' => Lang::get('auth.unresolvedRefundRequests')], 400);

            $this->iuUserRepository->createUserFeedback($user->id, $request->feedback, FeedbackTypeData::DELETE);
            $this->iuUserRepository->createRestoreUser($user->id);
            $this->logout();

            IuAccountTrashed::dispatch($user->id);

            Mail::to($user->userProfile->email)->queue(new IuAccountTrashedEmail($user));

            DB::commit();
            return response()->json(['message' => Lang::get('auth.accountTrashed')], 200);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: AuthController@delete', [$e->getMessage()]);
            if ($e->getCode() == 23000)
                return response()->json(['errors' => $e->getMessage()], 400);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function restore(RestoreUserRequest $request)
    {
        try {
            $restoreUser = $this->iuUserRepository->deleteRestoreUser($request->token);

            IuAccountRestored::dispatch($restoreUser->user_id);

            return response()->json(['message' => Lang::get('auth.userRestored')], 200);
        } catch (\Exception $e) {
            Log::error('Exception: AuthController@restore', [$e->getMessage()]);
            return response()->json(['errors' => Lang::get('auth.unauthenticated')], 401);
        }
    }

    public function requestForgotUsername(RequestForgotUsernameRequest $request)
    {
        $email = $request->email;
        $user = $this->findByEmail($email);
        if (!$user)
            return response()->json(['message' => Lang::get('auth.emailNotFound')]); // 200: user enumeration: not giving idea to user that it exists or not into DB

        Mail::to($email)->queue(new ForgotUsernameEmail($user, $user->name));
        return response()->json(['message' => Lang::get('auth.forgotUsernameSent')]);
    }

    public function requestPasswordReset(RequestPasswordResetRequest $request)
    {
        if (!$this->authRepository->checkIfUsernameExists($request->username))
            return response()->json(['message' => Lang::get('auth.usernameNotFound')]);

        $user = User::where('name', $request->username)->first();

        $model = in_array($user->role_id, [
            RoleData::ADMIN,
            RoleData::HEAD_ADMIN,
            RoleData::MASTER_ADMIN
        ]) ? 'adminProfile' : 'userProfile';

        $user->load($model);

        $passwordReset = $this->authRepository->createPasswordResetToken($user->name);
        Mail::to($user->$model->email)->queue(new PasswordResetEmail($user, $passwordReset->token));

        return response()->json(['message' => Lang::get('auth.passwordResetLinkSent')]);
    }

    public function checkPasswordReset(CheckPasswordResetTokenRequest $request)
    {
        if (!$this->authRepository->checkIfPasswordResetTokenExists($request->token))
            return response()->json(['errors' => Lang::get('auth.invalidToken')], 400);

        return response()->json(['message' => Lang::get('auth.passwordResetTokenValid')]);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        if (!$this->authRepository->checkIfPasswordResetTokenExists($request->token))
            return response()->json(['errors' => Lang::get('auth.invalidToken')], 400);

        $passwordReset = $this->authRepository->getPasswordReset($request->token);

        $this->authRepository->updateUserPassword($passwordReset->name, $request->password);
        $this->authRepository->deletePasswordReset($passwordReset->id);

        $user = $this->iuUserRepository->findByName($passwordReset->name);
        UserPasswordUpdated::dispatch($user->id, $request->password);

        return response()->json(['message' => Lang::get('auth.passwordResetSuccess')]);
    }

    public function changeIuPassword(IuChangePasswordRequest $request)
    {
        $user = $request->user();
        $this->authRepository->updateUserPassword($user->name, $request->password);
        UserPasswordUpdated::dispatch($user->id, $request->password);

        return response()->json(['message' => Lang::get('auth.iuPasswordResetSuccess')]);
    }

    public function resendVerificationCode(ResendVerificationCodeRequest $request)
    {
        if (!$this->authRepository->checkIfUsernameExists($request->username))
            return response()->json(['errors' => Lang::get('auth.usernameNotFound')], 400);

        $user = $this->iuUserRepository->findByName($request->username);
        if (!$user || $user->email_verified_at)
            return response()->json(['message' => Lang::get('auth.alreadyVerified')], 200);

        $verifyUser = $this->authRepository->createVerifyUser($user);

        $model = in_array($user->role_id, [
            RoleData::ADMIN,
            RoleData::HEAD_ADMIN,
            RoleData::MASTER_ADMIN
        ]) ? 'adminProfile' : 'userProfile';

        $user->load($model);

        Mail::to($user->$model->email)->queue(new VerificationEmail($user, $verifyUser->token));
        return response()->json(['message' => Lang::get('auth.verificationCodeSent')], 200);
    }

    public function resendParentVerificationCode(ResendParentVerificationCodeRequest $request)
    {
        $user = $this->iuUserRepository->findByName($request->username);
        if (!$user)
            return response()->json(['errors' => Lang::get('auth.usernameNotFound')], 400);

        if ($user->userProfile->email === $request->parentEmailAddress)
            return response()->json(['errors' => Lang::get('auth.invalidParentEmail')], 400);

        if ($user->age_verified_at)
            return response()->json(['message' => Lang::get('auth.alreadyVerified')], 200);

        $verifyAgeUser = $this->authRepository->createVerifyUserAge($user->id);
        Mail::to($request->parentEmailAddress)->queue(new AgeVerificationEmail($user, $user->userProfile, $verifyAgeUser->token));

        return response()->json(['message' => Lang::get('auth.parentVerificationCodeSent')], 200);
    }

    protected function setTokenExpiry($expiry)
    {
        return auth()->factory()->setTTL($expiry);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ], 200);
    }

    /**
     * Get username property.
     * Override AuthenticatesUsers traits's method
     * for changing default `email` to any other required field
     *
     * @return string
     */
    public function username()
    {
        return $this->username;
    }

    private function findByEmail($email)
    {
        return User::whereHas('userProfile', function ($q) use ($email) {
            $q->where('email', $email);
        })
            ->orWhereHas('adminProfile', function ($q) use ($email) {
                $q->where('email', $email);
            })
            ->first();
    }
}
