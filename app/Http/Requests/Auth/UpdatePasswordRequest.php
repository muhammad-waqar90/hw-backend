<?php

namespace App\Http\Requests\Auth;

use App\Repositories\AuthenticationRepository;
use App\Repositories\IU\IuUserRepository;
use App\Rules\NotFromPasswordHistory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    private IuUserRepository $iuUserRepository;
    private AuthenticationRepository $authRepository;

    public function __construct(IuUserRepository $iuUserRepository, AuthenticationRepository $authRepository)
    {
        $this->iuUserRepository = $iuUserRepository;
        $this->authRepository = $authRepository;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'token' => 'required|string|size:20',
            'password' => [
                'required', 'max:255', 'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
                new NotFromPasswordHistory($this->getUserByPasswordResetToken(request()->token), request()->password)
            ],
        ];
    }

    public function getUserByPasswordResetToken($token)
    {
        $passwordReset = $this->authRepository->getPasswordReset($token);
        return $this->iuUserRepository->findByName($passwordReset?->name);
    }
}
