<?php

namespace App\Http\Requests\IU;

use App\Rules\NotFromPasswordHistory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class IuChangePasswordRequest extends FormRequest
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
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required', 'max:255', 'confirmed', 'different:current_password',
                Password::min(8)->mixedCase()->numbers()->symbols(),
                new NotFromPasswordHistory(request()->user(), request()->password)
            ],
        ];
    }
}
