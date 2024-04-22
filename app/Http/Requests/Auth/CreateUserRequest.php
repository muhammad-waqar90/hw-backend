<?php

namespace App\Http\Requests\Auth;

use App\Rules\IsValidHCaptcha;
use Carbon\Carbon;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
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
            'first_name' => 'required|min:2|max:20',
            'last_name' => 'required|min:2|max:20',
            'email' => 'required|email|max:255|unique:user_profiles,email',
            'password' => ['required', 'max:255', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'dateOfBirth' => 'required|date|before_or_equal:' . Carbon::now()->subYears(1)->format('Y-m-d')
                . '|after_or_equal:' . Carbon::now()->subYears(100)->format('Y-m-d'),
            'termsAndConditionsAccepted' => 'required|boolean|in:1',
            'communicationAccepted' => 'present|boolean',
            // TODO: HCaptchaService::verify is not working properly in custom validation rule, showing dangling behaviour - required to test in detail before enabling it.
            // 'captchaToken' => new IsValidHCaptcha(request()->captchaToken)
        ];
    }

    public function withValidator($validator)
    {
        $validator->sometimes('parentEmailAddress', 'required|email|max:255|different:email', function ($input) {
            return $input->dateOfBirth > Carbon::now()->subYears(13)->format('Y-m-d');
        });
    }

    public function messages()
    {
        return [
            'parentEmailAddress.required' => 'Legal guardian\'s email address field is required.',
            'parentEmailAddress.different' => 'The legal guardian\'s email address and email must be different.',
            'parentEmailAddress.email' => 'The legal guardian\'s email address must be a valid email address.',
            'parentEmailAddress.max' => 'The legal guardian\'s email address may not be greater than 255 characters.'
        ];
    }
}
